<?php
namespace api;

class JWT {
    private static $secret = 'chave-secreta-muito-segura';

    public static function encode($payload) {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payloadEncoded = base64_encode(json_encode($payload));
        $signature = base64_encode(hash_hmac('sha256', "$header.$payloadEncoded", self::$secret, true));

        return "$header.$payloadEncoded.$signature";
    }

    public static function decode($token) {
        [$header, $payload, $signature] = explode('.', $token);
        $valid = base64_encode(hash_hmac('sha256', "$header.$payload", self::$secret, true));

        if (!hash_equals($signature, $valid)) return false;

        $dados = json_decode(base64_decode($payload), true);
        if (time() > $dados['exp']) return false;

        return $dados;
    }

    public static function gerarJWT($dados) {
        $payload = [
            'sub' => $dados['id'],
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'role' => $dados['role'],
            'exp' => time() + (60 * 60 * 2)
        ];
        return self::encode($payload);
    }

    public static function verificarJWT($token) {
        return self::decode($token);
    }
}
