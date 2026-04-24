<?php

$autoloadPath = __DIR__ . '/vendor/autoload.php';
$googleServicesAutoload = __DIR__ . '/vendor/google/apiclient-services/autoload.php';

if (is_file($autoloadPath) && is_file($googleServicesAutoload)) {
    require $autoloadPath;

    if (class_exists(\Dotenv\Dotenv::class)) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
    }
} else {
    $envPath = __DIR__ . '/.env';

    if (is_file($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
