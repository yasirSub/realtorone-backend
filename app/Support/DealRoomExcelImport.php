<?php

namespace App\Support;

use App\Models\Result;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use ZipArchive;

/**
 * Parses Deal Room–style .xlsx (first sheet) and upserts hot_lead rows for a user.
 * Expected columns (header row): Name, Contact number, Email, Lead Source, Lead Stage, Lead Type, …
 */
class DealRoomExcelImport
{
    /**
     * @return array{created: int, updated: int, skipped: int}
     */
    public static function importFromFilePath(int $userId, string $path): array
    {
        $rows = self::parseXlsx($path);

        return self::importFromRows($userId, $rows);
    }

    /**
     * @param  list<list<string>>  $rows  Raw rows including header as first row
     * @return array{created: int, updated: int, skipped: int}
     */
    public static function importFromRows(int $userId, array $rows): array
    {
        if (empty($rows)) {
            return ['created' => 0, 'updated' => 0, 'skipped' => 0];
        }

        $header = array_shift($rows);
        if (! is_array($header) || $header === []) {
            return ['created' => 0, 'updated' => 0, 'skipped' => 0];
        }

        /** Normalize header keys (trim, fix BOM) */
        $normHeader = [];
        foreach ($header as $i => $h) {
            $normHeader[$i] = trim((string) $h, " \t\n\r\0\x0B\xEF\xBB\xBF");
        }

        $nameCol = self::findNameColumnIndex($normHeader);

        $syncDate = now()->toDateString();
        $updatedCount = 0;
        $createdCount = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (! is_array($row)) {
                $skipped++;

                continue;
            }
            $data = [];
            foreach ($normHeader as $i => $h) {
                if ($h === '') {
                    continue;
                }
                if (isset($row[$i])) {
                    $data[$h] = $row[$i];
                }
            }

            $name = '';
            if ($nameCol !== null && isset($row[$nameCol])) {
                $name = trim((string) $row[$nameCol]);
            }
            if ($name === '' && isset($data['Name'])) {
                $name = trim((string) $data['Name']);
            }
            if ($name === '') {
                foreach (['Name', 'name', 'Client', 'Client name', 'Full name'] as $k) {
                    if (isset($data[$k]) && trim((string) $data[$k]) !== '') {
                        $name = trim((string) $data[$k]);
                        break;
                    }
                }
            }

            if ($name === '') {
                $skipped++;

                continue;
            }

            $contact = $data['Contact number'] ?? $data['Contact'] ?? $data['Phone'] ?? '';
            $email = trim((string) ($data['Email'] ?? $data['email'] ?? ''));
            $source = $data['Lead Source'] ?? $data['Source'] ?? 'Excel';
            $rawStage = strtolower(trim((string) ($data['Lead Stage'] ?? $data['Stage'] ?? 'cold calling')));
            $stage = match (true) {
                str_contains($rawStage, 'negotiat') => 'deal negotiation',
                str_contains($rawStage, 'site') => 'deal negotiation',
                str_contains($rawStage, 'closure') || str_contains($rawStage, 'close') => 'deal close',
                str_contains($rawStage, 'meeting') => 'client meeting',
                str_contains($rawStage, 'follow') => 'follow up back',
                default => CrmPipeline::normalizeLeadStageString($rawStage) ?? $rawStage,
            };
            $type = $data['Lead Type'] ?? $data['Type'] ?? 'Buyer';

            // Check if lead already exists for this user by email OR Name
            // If email is provided, we prioritize checking by email
            $leadQuery = Result::where('user_id', $userId)
                ->where('type', 'hot_lead');

            if ($email !== '') {
                $lead = (clone $leadQuery)
                    ->where('notes', 'LIKE', '%"email":"' . $email . '"%')
                    ->first();
            } else {
                $lead = (clone $leadQuery)
                    ->where('client_name', $name)
                    ->first();
            }

            // User requested: "if same mail id there dont add that" and "dont add them in the list"
            // We will skip rows that already exist instead of updating them to avoid duplicates
            if ($lead) {
                $skipped++;
                continue;
            }

            $baseNotes = [
                'lead_stage' => $stage,
                'email' => $email,
                'contact' => $contact,
                'lead_type' => $type,
                'synced_at' => now()->toDateTimeString(),
            ];

            $baseNotes['crm_started_at'] = now()->toIso8601String();
            Result::create([
                'user_id' => $userId,
                'date' => $syncDate,
                'type' => 'hot_lead',
                'client_name' => $name,
                'property_name' => $contact,
                'source' => $source,
                'value' => 0,
                'status' => 'active',
                'notes' => json_encode($baseNotes),
            ]);
            $createdCount++;
        }

        return [
            'created' => $createdCount,
            'updated' => 0, // We are skipping instead of updating now as per request
            'skipped' => $skipped,
        ];
    }

    /**
     * @param  array<int, string>  $headerRow
     */
    private static function findNameColumnIndex(array $headerRow): ?int
    {
        foreach ($headerRow as $i => $h) {
            $l = strtolower(trim($h));
            if (in_array($l, ['name', 'client name', 'full name', 'client'], true)) {
                return $i;
            }
        }

        return null;
    }

    /**
     * Minimal XLSX parser: maps each cell to its **column index** from `r` (A1, B2…).
     * Excel omits empty cells; the old sequential parser misaligned columns with the header.
     *
     * @return list<list<string>>
     */
    public static function parseXlsx(string $filename): array
    {
        $zip = new ZipArchive;
        if ($zip->open($filename) !== true) {
            Log::warning('DealRoomExcelImport: could not open zip/xlsx', ['path' => $filename]);

            return [];
        }

        $sharedStrings = [];
        if ($content = $zip->getFromName('xl/sharedStrings.xml')) {
            $xml = simplexml_load_string($content);
            if ($xml !== false) {
                foreach ($xml->si as $si) {
                    $sharedStrings[] = self::sharedStringFromSi($si);
                }
            }
        }

        $rows = [];
        if ($content = $zip->getFromName('xl/worksheets/sheet1.xml')) {
            $xml = simplexml_load_string($content);
            if ($xml !== false && isset($xml->sheetData->row)) {
                foreach ($xml->sheetData->row as $row) {
                    $byCol = [];
                    $maxCol = 0;
                    foreach ($row->c as $cell) {
                        $ref = (string) $cell['r'];
                        if ($ref === '') {
                            continue;
                        }
                        $colIdx = self::columnIndexFromCellRef($ref);
                        $byCol[$colIdx] = self::cellValue($cell, $sharedStrings);
                        if ($colIdx > $maxCol) {
                            $maxCol = $colIdx;
                        }
                    }
                    $rowData = [];
                    for ($i = 0; $i <= $maxCol; $i++) {
                        $rowData[$i] = $byCol[$i] ?? '';
                    }
                    $rows[] = $rowData;
                }
            }
        }
        $zip->close();

        return $rows;
    }

    private static function sharedStringFromSi(SimpleXMLElement $si): string
    {
        if (isset($si->t)) {
            return (string) $si->t;
        }
        $out = '';
        if (isset($si->r)) {
            foreach ($si->r as $r) {
                $out .= (string) $r->t;
            }
        }

        return $out;
    }

    /**
     * @param  array<int, string>  $sharedStrings
     */
    private static function cellValue(SimpleXMLElement $cell, array $sharedStrings): string
    {
        $t = (string) $cell['t'];
        if ($t === 'inlineStr' && isset($cell->is->t)) {
            return (string) $cell->is->t;
        }
        if ($t === 's') {
            $idx = (int) $cell->v;

            return $sharedStrings[$idx] ?? '';
        }
        if ($t === 'str') {
            return (string) $cell->v;
        }
        if ($t === 'b') {
            return ((string) $cell->v) === '1' ? '1' : '0';
        }

        return (string) $cell->v;
    }

    /** A=0, B=1, …, Z=25, AA=26 */
    private static function columnIndexFromCellRef(string $ref): int
    {
        if (! preg_match('/^([A-Z]+)\d+$/i', $ref, $m)) {
            return 0;
        }
        $col = strtoupper($m[1]);
        $index = 0;
        $len = strlen($col);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + ord($col[$i]) - ord('A') + 1;
        }

        return $index - 1;
    }

    /**
     * Extra guidance when the file parsed but no clients were created/updated.
     */
    public static function appendNoRowsHint(string $message, array $stats): string
    {
        if (($stats['created'] ?? 0) > 0 || ($stats['updated'] ?? 0) > 0) {
            return $message;
        }
        if (($stats['skipped'] ?? 0) < 1) {
            return $message;
        }

        return $message.' No client names were found in the data rows. Enter each name in the Name column (under the header), save the file, and upload again — an empty template only has headers and blank rows.';
    }
}
