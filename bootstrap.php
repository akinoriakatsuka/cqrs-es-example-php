<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

$projectRoot = __DIR__;
$defaultEnvFile = '.env';
$configuredEnvFile = $_SERVER['ENV_FILE'] ?? getenv('ENV_FILE') ?? $defaultEnvFile;
$configuredEnvFile = is_string($configuredEnvFile) && $configuredEnvFile !== ''
    ? $configuredEnvFile
    : $defaultEnvFile;

// Fall back to the default .env if the configured file does not exist.
$envFileToLoad = $configuredEnvFile;
if ($configuredEnvFile !== $defaultEnvFile && !is_file($projectRoot . '/' . $configuredEnvFile)) {
    $envFileToLoad = $defaultEnvFile;
}

Dotenv::createImmutable($projectRoot, $envFileToLoad)->safeLoad();
