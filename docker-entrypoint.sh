#!/usr/bin/env sh
set -eu

cd /var/www/html

# Wait for DB to be reachable (important on first boot)
if [ "${WAIT_FOR_DB:-1}" = "1" ]; then
  echo "Waiting for database at ${DB_HOST:-mysql}:${DB_PORT:-3306}..."
  php -r '
    $host = getenv("DB_HOST") ?: "mysql";
    $port = getenv("DB_PORT") ?: "3306";
    $db   = getenv("DB_DATABASE") ?: "";
    $user = getenv("DB_USERNAME") ?: "";
    $pass = getenv("DB_PASSWORD") ?: "";
    $dsn  = "mysql:host={$host};port={$port};dbname={$db}";
    $maxSeconds = 90;
    $start = time();
    while (true) {
      try {
        new PDO($dsn, $user, $pass, [PDO::ATTR_TIMEOUT => 2]);
        fwrite(STDOUT, "Database is reachable.\n");
        exit(0);
      } catch (Throwable $e) {
        if (time() - $start >= $maxSeconds) {
          fwrite(STDERR, "Timed out waiting for database: " . $e->getMessage() . "\n");
          exit(1);
        }
        usleep(500000);
      }
    }
  '
fi

# One-time app prep; safe on restarts
rm -rf public/storage || true
php artisan storage:link || true

if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
  php artisan migrate --force
fi

if [ "${RUN_SEED:-1}" = "1" ]; then
  php artisan db:seed --force || true
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"

