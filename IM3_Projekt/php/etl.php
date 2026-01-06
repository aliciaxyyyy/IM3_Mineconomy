<?php
require_once 'config.php';

/* ============================================================================
   HANDLUNGSANWEISUNG (extract.php)
   1) Lade Konfiguration/Constants (API-URL, Parameter, ggf. Zeitzone).
   2) Baue die Request-URL (Query-Params sauber via http_build_query).
   3) Initialisiere cURL (curl_init) mit der Ziel-URL.
   4) Setze cURL-Optionen (RETURNTRANSFER, TIMEOUT, HTTP-Header, FOLLOWLOCATION).
   5) Führe Request aus (curl_exec) und prüfe Transportfehler (curl_error).
   6) Prüfe HTTP-Status & Content-Type (JSON erwartet), sonst früh abbrechen.
   7) Dekodiere JSON robust (json_decode(..., true)).
   8) Normalisiere/prüfe Felder (defensive Defaults, Typen casten).
   9) Gib die Rohdaten als PHP-Array ZURÜCK (kein echo) für den Transform-Schritt.
  10) Fehlerfälle: Exception/Fehlerobjekt nach oben reichen (kein HTML ausgeben).
   ============================================================================ */


/**
 * Dieses Skript ruft automatisch die aktuellen Werte für Gold, Silber und Platin
 * von der MetalPriceAPI ab und speichert sie täglich in einer MySQL-Datenbank.
 */

// ====== 1. API-Daten abrufen ======
function fetchMineralValue() {
    $url = API_URL . '?api_key=' . API_KEY . '&base=' . API_BASE . '&currencies=' . API_CURRENCIES;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// ====== 2. Daten speichern ======
function saveToDatabase($gold, $silver, $platinum, $date) {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // prüft, ob bereits ein Eintrag für das Datum existiert
        $check = $pdo->prepare("SELECT id FROM `mineral value` WHERE date = ?");
        $check->execute([$date]);

        if ($check->rowCount() > 0) {
            echo "ℹ️  Für $date existiert bereits ein Datensatz.\n";
            return;
        }

        // neuen Eintrag speichern
        $stmt = $pdo->prepare("
            INSERT INTO `mineral value` (gold, silver, platinum, date)
            VALUES (:gold, :silver, :platinum, :date)
        ");
        $stmt->execute([
            ':gold' => $gold,
            ':silver' => $silver,
            ':platinum' => $platinum,
            ':date' => $date
        ]);

        echo "✅ Erfolgreich eingetragen für $date\n";

    } catch (PDOException $e) {
        echo "❌ Datenbankfehler: " . $e->getMessage() . "\n";
    }
}

// ====== 3. Hauptablauf ======
$data = fetchMineralValue();

if ($data && isset($data['rates'])) {

    // API liefert: 1 USD = XAU, also umkehren → 1/XAU = USD pro Unze
    $gold = isset($data['rates']['XAU']) ? round(1 / $data['rates']['XAU'], 2) : 0;
    $silver = isset($data['rates']['XAG']) ? round(1 / $data['rates']['XAG'], 2) : 0;
    $platinum = isset($data['rates']['XPT']) ? round(1 / $data['rates']['XPT'], 2) : 0;

    // Datum aus API oder fallback auf heute
    $date = isset($data['timestamp']) ? date('Y-m-d', $data['timestamp']) : date('Y-m-d');

    saveToDatabase($gold, $silver, $platinum, $date);

} else {
    echo "❌ API-Fehler – keine gültigen Daten erhalten.\n";
}
?>
