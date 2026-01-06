<?php
/* ============================================================================
   HANDLUNGSANWEISUNG (unload.php)
   1) Setze Header: Content-Type: application/json; charset=utf-8.
   2) Binde 001_config.php (PDO-Config) ein.
   3) Lies optionale Request-Parameter (z. B. location, limit, from/to) und validiere.
   4) Baue SELECT mit PREPARED STATEMENT (WHERE/ORDER BY/LIMIT je nach Parametern).
   5) Binde Parameter sicher (execute([...]) oder bindValue()).
   6) Hole Datensätze (fetchAll) – optional gruppieren/umformen fürs Frontend.
   7) Antworte IMMER als JSON (json_encode) – auch bei leeren Treffern ([]) .
   8) Setze sinnvolle HTTP-Statuscodes (400 für Bad Request, 404 bei 0 Treffern (Detail), 200 ok).
   9) Fehlerfall: 500 + { "error": "..." } (keine internen Details leaken).
  10) Keine HTML-Ausgabe; keine var_dump in Prod.
   ============================================================================ */

// 1) Setze Header: Content-Type: application/json; charset=utf-8
header('Content-Type: application/json; charset=utf-8');

// 2) Binde config.php (PDO-Config) ein
require_once 'config.php';

try {
    // 3) Lies optionale Request-Parameter und validiere / PDO
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
    $from = isset($_GET['from']) ? $_GET['from'] : null;
    $to = isset($_GET['to']) ? $_GET['to'] : null;
    
    // Validierung
    if ($limit < 1 || $limit > 1000) {
        http_response_code(400);
        echo json_encode(['error' => 'Limit muss zwischen 1 und 1000 liegen']);
        exit;
    }
    
    if ($from && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
        http_response_code(400);
        echo json_encode(['error' => 'From-Datum muss im Format YYYY-MM-DD sein']);
        exit;
    }
    
    if ($to && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
        http_response_code(400);
        echo json_encode(['error' => 'To-Datum muss im Format YYYY-MM-DD sein']);
        exit;
    }

    // Datenbankverbindung
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 4) Baue SELECT mit PREPARED STATEMENT
    $sql = "SELECT id, gold, silver, platinum, date FROM `mineral value`";
    $params = [];
    $conditions = [];

    // WHERE-Bedingungen basierend auf Parametern
    if ($from) {
        $conditions[] = "date >= :from";
        $params[':from'] = $from;
    }
    
    if ($to) {
        $conditions[] = "date <= :to";
        $params[':to'] = $to;
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    // ORDER BY und LIMIT
    $sql .= " ORDER BY date DESC LIMIT :limit";
    
    // 5) Binde Parameter sicher
    $stmt = $pdo->prepare($sql);
    
    // Binde alle Parameter
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    
    $stmt->execute();
    
    // 6) Hole Datensätze (fetchAll)
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 7) Antworte IMMER als JSON
    if (empty($results)) {
        // Leere Treffer, aber trotzdem 200 OK für Listen-Endpoints
        http_response_code(200);
        echo json_encode([]);
    } else {
        // Daten gefunden
        http_response_code(200);
        echo json_encode($results);
    }

} catch (PDOException $e) {
    // 9) Fehlerfall: 500 + { "error": "..." }
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler aufgetreten']);
} catch (Exception $e) {
    // Allgemeine Fehler
    http_response_code(500);
    echo json_encode(['error' => 'Ein unerwarteter Fehler ist aufgetreten']);
}
?>
