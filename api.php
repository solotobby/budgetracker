<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database configuration
$dbFile = __DIR__ . '/db_config.php';
if (file_exists($dbFile)) {
    require_once $dbFile;
} else {
    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'budgetracker');
    define('DB_USER', 'budget_user');
    define('DB_PASS', 'BudgetSecure2026!');
}

function getDb() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function getJsonInput() {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?: [];
}

$action = $_GET['action'] ?? '';

try {
    $db = getDb();

    // ── REGISTER ──
    if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = getJsonInput();
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if (strlen($username) < 2) {
            jsonResponse(['error' => 'Username must be at least 2 characters.'], 400);
        }
        if (strlen($password) < 6) {
            jsonResponse(['error' => 'Password must be at least 6 characters.'], 400);
        }

        // Check if username already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(?)");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            jsonResponse(['error' => 'This username is already taken. Please choose another.'], 400);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $uid = 'usr_' . bin2hex(random_bytes(8));
        $token = bin2hex(random_bytes(32));

        $stmt = $db->prepare("INSERT INTO users (uid, username, password_hash, token, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$uid, $username, $hash, $token]);

        jsonResponse([
            'success' => true,
            'user' => [
                'uid' => $uid,
                'username' => $username,
                'token' => $token
            ]
        ]);
    }

    // ── LOGIN ──
    if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = getJsonInput();
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if (!$username || !$password) {
            jsonResponse(['error' => 'Username and password are required.'], 400);
        }

        $stmt = $db->prepare("SELECT id, uid, username, password_hash FROM users WHERE LOWER(username) = LOWER(?)");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            jsonResponse(['error' => 'Invalid username or password.'], 401);
        }

        $token = bin2hex(random_bytes(32));
        $stmt = $db->prepare("UPDATE users SET token = ? WHERE id = ?");
        $stmt->execute([$token, $user['id']]);

        jsonResponse([
            'success' => true,
            'user' => [
                'uid' => $user['uid'],
                'username' => $user['username'],
                'token' => $token
            ]
        ]);
    }

    // ── AUTH MIDDLEWARE HELPER ──
    function authUser($db) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = '';
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }
        if (!$token) {
            jsonResponse(['error' => 'Unauthorized: missing token'], 401);
        }

        $stmt = $db->prepare("SELECT id, uid, username FROM users WHERE token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        if (!$user) {
            jsonResponse(['error' => 'Session expired or invalid. Please sign in again.'], 401);
        }
        return $user;
    }

    // ── GET MONTH BUDGET ──
    if ($action === 'get_budget' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $user = authUser($db);
        $month = $_GET['month'] ?? '';
        if (!$month) {
            jsonResponse(['error' => 'Missing month parameter'], 400);
        }

        $stmt = $db->prepare("SELECT data_json, updated_at FROM budgets WHERE user_id = ? AND month_key = ?");
        $stmt->execute([$user['id'], $month]);
        $row = $stmt->fetch();

        if ($row) {
            $data = json_decode($row['data_json'], true);
            jsonResponse(['success' => true, 'data' => $data]);
        } else {
            jsonResponse(['success' => true, 'data' => null]);
        }
    }

    // ── SAVE MONTH BUDGET ──
    if ($action === 'save_budget' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $user = authUser($db);
        $input = getJsonInput();
        $month = trim($input['month'] ?? '');
        $data = $input['data'] ?? null;

        if (!$month || $data === null) {
            jsonResponse(['error' => 'Invalid budget data payload'], 400);
        }

        $json = json_encode($data);
        $stmt = $db->prepare("
            INSERT INTO budgets (user_id, month_key, data_json, updated_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE data_json = VALUES(data_json), updated_at = NOW()
        ");
        $stmt->execute([$user['id'], $month, $json]);

        jsonResponse(['success' => true]);
    }

    // Unknown action
    jsonResponse(['error' => 'Endpoint action not found'], 404);

} catch (Exception $e) {
    jsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
}
