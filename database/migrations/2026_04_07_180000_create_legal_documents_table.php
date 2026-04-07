<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->string('title', 255);
            $table->longText('body_html');
            $table->timestamps();
        });

        $now = now();
        $defaultPrivacy = <<<'HTML'
<h1>Privacy Policy</h1>
<p><strong>Last updated:</strong> April 7, 2026</p>
<p>RealtorOne (“we”, “us”, or “our”) provides coaching, training, and related tools through our mobile applications and websites. This Privacy Policy describes how we collect, use, disclose, and safeguard information when you use our services.</p>
<h2>1. Information we collect</h2>
<p>We may collect account and profile data, usage and device data, content you submit, communications with support, and push notification tokens if you opt in.</p>
<h2>2. How we use information</h2>
<p>We use information to provide and improve the Services, secure accounts, send service messages, analyze aggregated usage, and comply with law.</p>
<h2>3. Sharing</h2>
<p>We do not sell your personal information. We may share data with service providers under appropriate safeguards, or when required by law.</p>
<h2>4. Contact</h2>
<p>For privacy questions, contact us through support channels listed in the app. Replace this placeholder with your final counsel-reviewed text using the admin panel.</p>
HTML;
        $defaultTerms = <<<'HTML'
<h1>Terms &amp; Conditions</h1>
<p><strong>Last updated:</strong> April 7, 2026</p>
<p>These Terms govern your access to and use of RealtorOne applications, websites, and related services. By using the Services, you agree to these Terms.</p>
<h2>1. Use of the Services</h2>
<p>You agree to use the Services only in compliance with applicable law and our acceptable use standards.</p>
<h2>2. Disclaimers</h2>
<p>The Services are provided “as is” to the fullest extent permitted by law.</p>
<h2>3. Contact</h2>
<p>For questions about these Terms, contact us through support. Replace this placeholder with your final counsel-reviewed text using the admin panel.</p>
HTML;

        DB::table('legal_documents')->insert([
            [
                'slug' => 'privacy',
                'title' => 'Privacy Policy',
                'body_html' => $defaultPrivacy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'terms',
                'title' => 'Terms & Conditions',
                'body_html' => $defaultTerms,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};
