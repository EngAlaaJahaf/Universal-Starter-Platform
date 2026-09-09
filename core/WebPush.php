<?php

class WebPush
{
    public static function send(array $subscription, array $payload)
    {
        if (!PUSH_VAPID_PUBLIC_KEY || !PUSH_VAPID_PRIVATE_KEY || empty($subscription['endpoint'])) return false;
        $endpoint = $subscription['endpoint']; $audience = parse_url($endpoint, PHP_URL_SCHEME) . '://' . parse_url($endpoint, PHP_URL_HOST);
        $header = self::b64(json_encode(array('typ'=>'JWT','alg'=>'ES256'))); $claims=self::b64(json_encode(array('aud'=>$audience,'exp'=>time()+43200,'sub'=>PUSH_VAPID_SUBJECT)));
        $signature=''; $private=PUSH_VAPID_PRIVATE_KEY; if(!openssl_sign($header.'.'.$claims,$signature,$private,OPENSSL_ALGO_SHA256))return false;
        $jwt=$header.'.'.$claims.'.'.self::b64($signature); $ch=curl_init($endpoint); curl_setopt_array($ch,array(CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_HTTPHEADER=>array('TTL: 60','Content-Length: 0','Authorization: vapid t='.$jwt.', k='.PUSH_VAPID_PUBLIC_KEY)));$result=curl_exec($ch);$status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);return $status>=200&&$status<300;
    }
    private static function b64($value){return rtrim(strtr(base64_encode($value),'+/','-_'),'=');}
}
