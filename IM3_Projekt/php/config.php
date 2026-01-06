<?php
/**
 * Konfigurationsdatei
 * Enthält alle Zugangsdaten für Datenbank und API
 * Lädt Zugangsdaten aus einer lokalen .env Datei (nicht getrackt)
 */

$envPath = dirname(__DIR__) . '/.env';

if (is_readable($envPath)) {
	$env = parse_ini_file($envPath, false, INI_SCANNER_RAW);

	if (is_array($env)) {
		foreach ($env as $key => $value) {
			putenv($key . '=' . $value);
			$_ENV[$key] = $value;
		}
	}
} else {
	error_log('Hinweis: .env nicht gefunden - es werden Standardwerte verwendet.');
}

function env_value(string $key, $default = null) {
	$value = getenv($key);
	return $value === false ? $default : $value;
}

// Datenbankverbindung - Konstanten definieren
define('DB_HOST', env_value('DB_HOST', ''));
define('DB_NAME', env_value('DB_NAME', ''));
define('DB_USER', env_value('DB_USER', ''));
define('DB_PASS', env_value('DB_PASS', ''));
define('DB_CHARSET', env_value('DB_CHARSET', 'utf8'));

// API-Konfiguration für MetalPriceAPI
define('API_URL', env_value('API_URL', 'https://api.metalpriceapi.com/v1/latest'));
define('API_KEY', env_value('API_KEY', ''));
define('API_BASE', env_value('API_BASE', 'USD'));
define('API_CURRENCIES', env_value('API_CURRENCIES', 'XAU,XAG,XPT'));
?>