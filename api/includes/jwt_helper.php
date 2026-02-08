<?php
/**
 * Simple JWT utility for PHP
 */
class JWT {
    private static $key = "CHANGE_THIS_SECRET_KEY_IN_PRODUCTION"; // Idéalement dans .env

    public static function encode($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$key, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function decode($jwt) {
        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) < 3) return null;

        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
        $signatureProvided = $tokenParts[2];

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signatureCheck = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$key, true);
        $base64UrlSignatureCheck = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signatureCheck));

        if ($base64UrlSignatureCheck !== $signatureProvided) {
            return null;
        }

        return json_decode($payload, true);
    }
}
