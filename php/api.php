<?php
require_once 'config.php';

// Always default to "today" (1 day) when the page is first loaded
// Only use URL parameter if it's a valid period and not the initial page load
$period = '1'; // Always start with "today"

// If there's a specific period requested via AJAX or form submission, use it
if (isset($_GET['period']) && in_array($_GET['period'], ['1', '7', '30'])) {
    $period = $_GET['period'];
}

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    switch($period) {
        case '1':
        default:
            $stmt = $pdo->prepare("SELECT date, gold, silver, platinum FROM `mineral value` WHERE date = CURDATE() ORDER BY date DESC LIMIT 1");
            $chartTitle = "Heutige Edelmetallpreise";
            break;
        case '30':
            $stmt = $pdo->prepare("SELECT date, gold, silver, platinum FROM `mineral value` WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY date ASC");
            $chartTitle = "Edelmetallpreise der letzten 30 Tage";
            break;
        case '7':
            $stmt = $pdo->prepare("SELECT date, gold, silver, platinum FROM `mineral value` WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ORDER BY date ASC");
            $chartTitle = "Edelmetallpreise der letzten 7 Tage";
            break;
    }
    
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dates = [];
    $goldData = [];
    $silverData = [];
    $platinumData = [];

    foreach ($results as $row) {
        $dates[] = $row['date'];
        $goldData[] = $row['gold'];
        $silverData[] = $row['silver'];
        $platinumData[] = $row['platinum'];
    }

    // Handle AJAX requests
    if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
        header('Content-Type: application/json');
        echo json_encode([
            'dates' => $dates,
            'goldData' => $goldData,
            'silverData' => $silverData,
            'platinumData' => $platinumData,
            'period' => $period
        ]);
        exit;
    }

} catch (PDOException $e) {
    if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
    die("Datenbankfehler: " . $e->getMessage());
}
?>