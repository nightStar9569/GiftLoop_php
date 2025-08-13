<?php
// Bootstrap & common helpers

$config = require __DIR__ . '/config.php';

// Allow CORS for local development (reflect origin when present)
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($requestOrigin) {
	header('Access-Control-Allow-Origin: ' . $requestOrigin);
	header('Vary: Origin');
	header('Access-Control-Allow-Credentials: true');
} else {
	// Same-origin requests: reflect current host to avoid mismatches (localhost vs 127.0.0.1)
	$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
	$selfOrigin = ($isHttps ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
	header('Access-Control-Allow-Origin: ' . $selfOrigin);
	header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(204);
	exit;
}

// JSON helper
function json_response($data, int $status = 200): void {
	http_response_code($status);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($data, JSON_UNESCAPED_UNICODE);
	exit;
}

// Database connection (PDO)
function db(): PDO {
	static $pdo = null;
	if ($pdo) return $pdo;
	$cfg = require __DIR__ . '/config.php';
	$dsn = sprintf('%s:host=%s;dbname=%s;charset=%s',
		$cfg['db']['driver'], $cfg['db']['host'], $cfg['db']['database'], $cfg['db']['charset']);
	$pdo = new PDO($dsn, $cfg['db']['username'], $cfg['db']['password'], [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	]);
	return $pdo;
}

// Session setup
function start_app_session(): void {
	$cfg = require __DIR__ . '/config.php';
	if (session_status() === PHP_SESSION_NONE) {
		session_name($cfg['session']['name']);
		session_set_cookie_params([
			'lifetime' => $cfg['session']['cookie_lifetime'],
			'path' => '/',
			'secure' => $cfg['session']['secure'],
			'httponly' => $cfg['session']['httponly'],
			'samesite' => $cfg['session']['samesite'],
		]);
		session_start();
	}
}

function current_user() {
	start_app_session();
	return $_SESSION['user'] ?? null;
}

function require_auth(): array {
	$user = current_user();
	if (!$user) json_response(['error' => 'Unauthorized'], 401);
	return $user;
}

function is_admin(): bool {
	$user = current_user();
	return $user && !empty($user['is_admin']);
}

// Ensure database schema exists (runs once lazily)
function ensure_database_initialized(): void {
	$cfg = require __DIR__ . '/config.php';
	try {
		$pdo = db();
		// Probe for a core table
		$pdo->query('SELECT 1 FROM users LIMIT 1');
	} catch (Throwable $e) {
		// If database is missing, attempt to create it using a server-level connection
		$needsReconnect = false;
		if ($e instanceof PDOException) {
			// MySQL unknown database error code is 1049
			if ((int)$e->errorInfo[1] === 1049 || stripos($e->getMessage(), 'Unknown database') !== false) {
				$serverDsn = sprintf('%s:host=%s;charset=%s', $cfg['db']['driver'], $cfg['db']['host'], $cfg['db']['charset']);
				$serverPdo = new PDO($serverDsn, $cfg['db']['username'], $cfg['db']['password'], [
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				]);
				$dbName = preg_replace('/[^a-zA-Z0-9_]+/', '_', $cfg['db']['database']);
				$collation = $cfg['db']['collation'] ?? 'utf8mb4_unicode_ci';
				$charset = $cfg['db']['charset'] ?? 'utf8mb4';
				$serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$charset} COLLATE {$collation}");
				$needsReconnect = true;
			}
		}
		if ($needsReconnect) {
			// Reconnect to the newly created database
			$pdo = db();
		} else {
			// If other connection error, rethrow
			// to be handled by caller (and surfaced in debug)
			throw $e;
		}

		// Apply schema
		$schemaPath = __DIR__ . '/schema.sql';
		if (is_file($schemaPath)) {
			$sql = file_get_contents($schemaPath) ?: '';
			// Remove single-line comments
			$sql = preg_replace('/^--.*$/m', '', $sql);
			// Split by semicolons while keeping statements
			$statements = array_filter(array_map('trim', preg_split('/;\s*(?:\r?\n|$)/', $sql)));
			foreach ($statements as $stmt) {
				if ($stmt !== '') {
					$pdo->exec($stmt);
				}
			}
		}
	}
}

// Lightweight runtime migrations for incremental columns
function ensure_runtime_migrations(): void {
	try {
		$pdo = db();
		$col = $pdo->prepare("SELECT COUNT(*) AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'avatar_url'");
		$col->execute();
		$exists = (int)$col->fetch()['c'] > 0;
		if (!$exists) {
			$pdo->exec("ALTER TABLE users ADD COLUMN avatar_url TEXT NULL");
		}
	} catch (Throwable $e) {
		// Best-effort; ignore in production path
	}
}