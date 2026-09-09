<?php

class ApiV1BaseController extends Controller
{
    protected $apiKeyData = null;

    /**
     * Authenticate incoming request via Bearer Token or query param api_key
     * and verify that the key has the required scope.
     */
    protected function requireScope($requiredScope = 'all')
    {
        $token = $this->extractBearerToken();
        if (empty($token)) {
            $this->jsonError('Missing API Key. Please provide Authorization: Bearer <TOKEN> or ?api_key=<TOKEN>', 401);
        }

        $tokenHash = hash('sha256', $token);
        $db = new Database();
        $key = $db->fetch("SELECT * FROM api_keys WHERE key_hash = :hash AND is_active = 1", [':hash' => $tokenHash]);

        if (!$key) {
            $this->jsonError('Invalid or deactivated API Key.', 401);
        }

        if (!empty($key['expires_at']) && strtotime($key['expires_at']) < time()) {
            $this->jsonError('API Key has expired.', 403);
        }

        // Parse scopes
        $scopes = json_decode($key['scopes'] ?? '[]', true) ?: [];
        if (!in_array('all', $scopes) && !in_array($requiredScope, $scopes)) {
            $this->jsonError("Forbidden: API Key lacks the required scope [{$requiredScope}]. Allowed scopes: " . implode(', ', $scopes), 403);
        }

        // Update stats
        $db->query("UPDATE api_keys SET requests_count = requests_count + 1, last_used_at = CURRENT_TIMESTAMP WHERE id = :id", [':id' => $key['id']]);

        $this->apiKeyData = $key;
        return $key;
    }

    protected function extractBearerToken()
    {
        // 1. Authorization header
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? ($headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? ''));

        if (!empty($authHeader) && preg_match('/Bearer\s+(.*)$/i', $authHeader, $m)) {
            return trim($m[1]);
        }

        // 2. Query param ?api_key=... or ?token=...
        return trim((string) ($_GET['api_key'] ?? ($_GET['token'] ?? '')));
    }

    protected function jsonSuccess($data, $meta = [], $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, Accept');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        
        echo json_encode([
            'success' => true,
            'data'    => $data,
            'meta'    => array_merge([
                'timestamp' => date('c'),
                'version'   => 'v1',
            ], $meta)
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function jsonError($message, $code = 400, $details = [])
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, Accept');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

        echo json_encode([
            'success' => false,
            'error'   => [
                'code'    => $code,
                'message' => $message,
                'details' => $details
            ],
            'meta'    => [
                'timestamp' => date('c'),
                'version'   => 'v1'
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function getJsonBody()
    {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        return is_array($json) ? $json : $_POST;
    }
}
