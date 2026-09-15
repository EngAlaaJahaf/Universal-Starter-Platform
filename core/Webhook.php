<?php

/**
 * Webhooks (SH-12c): HMAC-SHA256 signature helpers plus a curl-based
 * deliver() that never throws. The signature half is pure and unit-tested;
 * the transient half fails soft so callers (publish pipeline, craft CLI)
 * can log-and-continue instead of breaking the request.
 */
class Webhook
{
    /**
     * Deterministic signature: "sha256=<hex>" over the raw JSON payload.
     */
    public static function signature($payload, $secret)
    {
        return 'sha256=' . hash_hmac('sha256', (string) $payload, (string) $secret);
    }

    /**
     * Constant-time check of an X-Webhook-Signature header value.
     */
    public static function verifySignature($payload, $secret, $provided)
    {
        if (!is_string($provided) || $provided === '') {
            return false;
        }
        return hash_equals(self::signature($payload, $secret), $provided);
    }

    /**
     * POST {event, data, timestamp} as JSON with a signed X-Webhook-Signature
     * header when $secret is non-empty. Returns {ok, status, error}; ok is
     * true for any 2xx. Network failures surface as ok=false + error string.
     */
    public static function deliver($url, $event, array $data = array(), $secret = '', array $extraHeaders = array())
    {
        $payload = json_encode(
            array('event' => $event, 'data' => $data, 'timestamp' => time()),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $headers = array_merge(array(
            'Content-Type: application/json',
            'User-Agent: UniversalStarterPlatform/1.0',
            'X-Webhook-Event: ' . $event
        ), $extraHeaders);

        if ($secret !== '') {
            $headers[] = 'X-Webhook-Signature: ' . self::signature($payload, $secret);
        }

        $curl = curl_init((string) $url);
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true
        ));
        $body = curl_exec($curl);
        $code = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        return array(
            'ok'     => $code >= 200 && $code < 300,
            'status' => $code,
            'error'  => $error === '' ? null : $error,
            'body'   => is_string($body) ? substr($body, 0, 500) : null
        );
    }
}