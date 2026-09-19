#!/bin/sh
set -e

cd /var/www/html

echo "[entrypoint] Menunggu database siap..."
tries=0
max_tries=30
until php -r '
    $env = [];
    if (is_file(".env")) {
        foreach (file(".env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === "" || $line[0] === "#" || !str_contains($line, "=")) {
                continue;
            }
            [$k, $v] = explode("=", $line, 2);
            $env[trim($k)] = trim(trim($v), "\"'"'"'");
        }
    }
    $host = $env["database.default.hostname"] ?? "localhost";
    $user = $env["database.default.username"] ?? "";
    $pass = $env["database.default.password"] ?? "";
    $name = $env["database.default.database"] ?? "";
    $port = (int) ($env["database.default.port"] ?? 3306);
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @mysqli_connect($host, $user, $pass, $name, $port);
    exit($conn ? 0 : 1);
' >/dev/null 2>&1; do
    tries=$((tries + 1))
    if [ "$tries" -ge "$max_tries" ]; then
        echo "[entrypoint] Database tidak siap setelah ${max_tries} percobaan, keluar." >&2
        exit 1
    fi
    echo "[entrypoint] Database belum siap (percobaan ${tries}/${max_tries}), tunggu 2s..."
    sleep 2
done

echo "[entrypoint] Menjalankan migrasi..."
php spark migrate

echo "[entrypoint] Migrasi selesai. Menjalankan proses utama..."
exec "$@"
