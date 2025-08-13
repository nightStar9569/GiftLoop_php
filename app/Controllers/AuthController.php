<?php
namespace App\Controllers;

use PDO;

class AuthController
{
    public function register(): void
    {
        $data = read_json_body();
        $email = strtolower(sanitize_email($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $first = trim((string) ($data['firstName'] ?? ''));
        $last = trim((string) ($data['lastName'] ?? ''));
        $birth = !empty($data['birthDate']) ? $data['birthDate'] : null;
        if (!$email || !$password)
            json_response(['error' => 'Invalid input'], 422);
        if (strlen($email) > 190)
            json_response(['error' => 'Email too long'], 422);
        if (strlen($password) < 8)
            json_response(['error' => 'Password too short'], 422);
        if ($first !== null && strlen($first) > 100)
            $first = substr($first, 0, 100);
        if ($last !== null && strlen($last) > 100)
            $last = substr($last, 0, 100);

        $pdo = db();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch())
            json_response(['error' => 'Email already registered'], 409);

        $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, first_name, last_name, birth_date) VALUES (?,?,?,?,?)');
        $stmt->execute([$email, hash_password($password), $first, $last, $birth]);
        $id = (int) $pdo->lastInsertId();
        start_app_session();
        $_SESSION['user'] = [
            'id' => $id,
            'email' => $email,
            'firstName' => $first,
            'lastName' => $last,
            'membershipLevel' => 'basic',
            'points' => 0,
            'giftsReceived' => 0,
            'giftsSent' => 0,
            'is_admin' => 0,
        ];
        json_response(['user' => $_SESSION['user']]);
    }

    public function login(): void
    {
        $data = read_json_body();
        $email = strtolower(sanitize_email($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        if (!$email || !$password)
            json_response(['error' => 'Invalid input'], 422);
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user || !verify_password($password, $user['password_hash'])) {
            json_response(['error' => 'Invalid credentials'], 401);
        }
        start_app_session();
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'email' => $user['email'],
            'firstName' => $user['first_name'],
            'lastName' => $user['last_name'],
            'membershipLevel' => $user['membership_level'],
            'points' => (int) $user['points'],
            'giftsReceived' => (int) $user['gifts_received'],
            'giftsSent' => (int) $user['gifts_sent'],
            'is_admin' => (int) $user['is_admin'],
        ];
        json_response(['user' => $_SESSION['user']]);
    }

    public function logout(): void
    {
        start_app_session();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
        }
        session_destroy();
        json_response(['ok' => true]);
    }

    public function me(): void
    {
        $sessionUser = current_user();
        if (!$sessionUser) json_response(['user' => null]);
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id, email, first_name, last_name, membership_level, points, gifts_received, gifts_sent, is_admin, birth_date, avatar_url FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$sessionUser['id']]);
        $row = $stmt->fetch();
        if (!$row) json_response(['user' => $sessionUser]);
        $user = [
            'id' => (int)$row['id'],
            'email' => $row['email'],
            'firstName' => $row['first_name'],
            'lastName' => $row['last_name'],
            'membershipLevel' => $row['membership_level'],
            'points' => (int)($row['points'] ?? 0),
            'giftsReceived' => (int)($row['gifts_received'] ?? 0),
            'giftsSent' => (int)($row['gifts_sent'] ?? 0),
            'is_admin' => (int)($row['is_admin'] ?? 0),
            'birthDate' => $row['birth_date'] ?? null,
            'avatarUrl' => $row['avatar_url'] ?? null,
        ];
        // Refresh session for downstream usage
        start_app_session();
        $_SESSION['user'] = $user;
        json_response(['user' => $user]);
    }

    private function getConfig(): array
    {
        return require __DIR__ . '/../../api/config.php';
    }

    private function getBaseUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $cfg = $this->getConfig();
        $basePath = rtrim((string) ($cfg['app']['base_path'] ?? ''), '/');
        return sprintf('%s://%s%s', $scheme, $host, $basePath);
    }

    public function googleStart(): void
    {
        $cfg = $this->getConfig();
        $clientId = trim((string) ($cfg['oauth']['google']['client_id'] ?? ''));
        if ($clientId === '') {
            $back = $this->getBaseUrl() . '/auth.php?oauth_error=google_not_configured';
            header('Location: ' . $back, true, 302);
            exit;
        }
        start_app_session();
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;
        $callback = $this->getBaseUrl() . '/api/auth/google/callback';
        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $callback,
            'response_type' => 'code',
            // Request birthday read scope (requires People API enabled and test users for sensitive scope)
            'scope' => 'openid email profile https://www.googleapis.com/auth/user.birthday.read',
            'access_type' => 'online',
            'include_granted_scopes' => 'true',
            'state' => $state,
            'prompt' => 'select_account',
        ];
        $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        header('Location: ' . $url, true, 302);
        exit;
    }

    public function googleCallback(): void
    {
        $cfg = $this->getConfig();
        start_app_session();
        $expectedState = $_SESSION['oauth_state'] ?? '';
        $receivedState = (string) ($_GET['state'] ?? '');
        if (!$expectedState || !hash_equals($expectedState, $receivedState)) {
            $back = $this->getBaseUrl() . '/auth.php?oauth_error=invalid_state';
            header('Location: ' . $back, true, 302);
            exit;
        }
        unset($_SESSION['oauth_state']);

        $code = (string) ($_GET['code'] ?? '');
        if ($code === '') {
            $back = $this->getBaseUrl() . '/auth.php?oauth_error=token_exchange_failed';
            header('Location: ' . $back, true, 302);
            exit;
        }

        $clientId = trim((string) ($cfg['oauth']['google']['client_id'] ?? ''));
        $clientSecret = trim((string) ($cfg['oauth']['google']['client_secret'] ?? ''));
        $redirectUri = $this->getBaseUrl() . '/api/auth/google/callback';

        // Exchange code for tokens
        $tokenResponse = $this->httpPostJson('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        if (!is_array($tokenResponse) || empty($tokenResponse['access_token'])) {
            $back = $this->getBaseUrl() . '/auth.php?oauth_error=token_exchange_failed';
            header('Location: ' . $back, true, 302);
            exit;
        }

        $accessToken = $tokenResponse['access_token'];

        // Fetch basic profile
        $userinfo = $this->httpGetJson('https://www.googleapis.com/oauth2/v2/userinfo', $accessToken);
        if (!is_array($userinfo) || empty($userinfo['email'])) {
            $back = $this->getBaseUrl() . '/auth.php?oauth_error=userinfo_failed';
            header('Location: ' . $back, true, 302);
            exit;
        }

        // Attempt to fetch birthday via People API (requires scope and API enabled)
        $birthDateStr = null;
        $people = $this->httpGetJson('https://people.googleapis.com/v1/people/me?personFields=birthdays', $accessToken);
        if (isset($people['birthdays']) && is_array($people['birthdays'])) {
            foreach ($people['birthdays'] as $b) {
                $d = $b['date'] ?? null; // ['year'=>YYYY,'month'=>M,'day'=>D] (year may be missing)
                if (is_array($d) && !empty($d['month']) && !empty($d['day'])) {
                    if (!empty($d['year'])) {
                        $year = (int) $d['year'];
                        $month = (int) $d['month'];
                        $day = (int) $d['day'];
                        $birthDateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        break;
                    }
                }
            }
        }

        $email = strtolower(sanitize_email((string) $userinfo['email']));
        $first = trim((string) ($userinfo['given_name'] ?? ''));
        $last = trim((string) ($userinfo['family_name'] ?? ''));

        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            // Create user with a random password placeholder
            $placeholder = bin2hex(random_bytes(16));
            if ($birthDateStr) {
                $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, first_name, last_name, birth_date) VALUES (?,?,?,?,?)');
                $stmt->execute([$email, hash_password($placeholder), $first, $last, $birthDateStr]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, first_name, last_name) VALUES (?,?,?,?)');
                $stmt->execute([$email, hash_password($placeholder), $first, $last]);
            }
            $userId = (int) $pdo->lastInsertId();
            $user = [
                'id' => $userId,
                'email' => $email,
                'first_name' => $first,
                'last_name' => $last,
                'membership_level' => 'basic',
                'points' => 0,
                'gifts_received' => 0,
                'gifts_sent' => 0,
                'is_admin' => 0,
            ];
            if ($birthDateStr) {
                $user['birth_date'] = $birthDateStr;
            }
        } else {
            // If user exists without birth_date and we have it now, update
            if ($birthDateStr && empty($user['birth_date'])) {
                $upd = $pdo->prepare('UPDATE users SET birth_date = ? WHERE id = ?');
                $upd->execute([$birthDateStr, (int) $user['id']]);
                $user['birth_date'] = $birthDateStr;
            }
        }

        // Establish session
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'email' => $user['email'],
            'firstName' => $user['first_name'],
            'lastName' => $user['last_name'],
            'membershipLevel' => $user['membership_level'] ?? 'basic',
            'points' => (int) ($user['points'] ?? 0),
            'giftsReceived' => (int) ($user['gifts_received'] ?? 0),
            'giftsSent' => (int) ($user['gifts_sent'] ?? 0),
            'is_admin' => (int) ($user['is_admin'] ?? 0),
            'birthDate' => $user['birth_date'] ?? null,
        ];

        // Redirect to app after login (prime localStorage for smoother UX)
        $redirectPath = (string) ($cfg['oauth']['redirect_after_login'] ?? '/mypage.php');
        $hasLeadingSlash = (bool) preg_match('#^/#', $redirectPath);
        $redirectUrl = $this->getBaseUrl() . ($hasLeadingSlash ? $redirectPath : ('/' . $redirectPath));

        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Logging you in...</title></head><body>';
        echo '<script>(function(){try{localStorage.setItem("isLoggedIn","true");}catch(_){};
          (function(){try{fetch("' . $this->getBaseUrl() . '/api/auth/me",{credentials:"include"}).then(r=>r.json()).then(d=>{if(d&&d.user){try{localStorage.setItem("userData",JSON.stringify(d.user));}catch(_){}}}).catch(()=>{});}catch(_){}})();
          setTimeout(function(){ window.location.replace("' . $redirectUrl . '"); }, 50);
        })();</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') . '">ログイン完了。自動的に遷移しない場合は<a href="' . htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') . '">こちら</a>をクリックしてください。</noscript>';
        echo '</body></html>';
        exit;
    }

    private function httpPostJson(string $url, array $fields): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($response === false)
            return [];
        $json = json_decode($response, true);
        return is_array($json) ? $json : [];
    }

    private function httpGetJson(string $url, string $accessToken): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($response === false)
            return [];
        $json = json_decode($response, true);
        return is_array($json) ? $json : [];
    }
}