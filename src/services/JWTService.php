<?php

declare(strict_types=1);

namespace App\services;

class JWTService
{
    private static ?string $key = null;

    private static function initKey(): void {
        if (self::$key === null) {
            self::$key = $_ENV['JWT_SECRET_KEY'] ?? '';
            if (empty(self::$key)) throw new \Exception('Secret key JWT is not defined (JWT_SECRET_KEY)');
        }
    }


    public static function generate(array $payload) : string {
        
        self::initKey();

        // header 
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        // payload avec expiration 24H
        $payload['exp'] = time() + (24 * 60 *60);
            //   $payload['exp'] = time() + (30);// test 30sec 

        // Encoder Header et payload 
        $base64Header = self::base64url_encode(json_encode($header));
        $base64Payload = self::base64url_encode(json_encode($payload));


        //Création de la signature
        $signature = hash_hmac(
            'sha256', 
            $base64Header.'.'. $base64Payload, 
            self::$key,
            true
        );
        $base64Signature = self::base64url_encode($signature);




        
        
        
        return $base64Header . '.' . $base64Payload . '.' . $base64Signature; 

    }
    private static function base64url_encode(string $data): string {

        return  rtrim(strtr(base64_encode($data), '+/', '-_'),'=');
    }

    private static function base64url_decode(string $data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function verify(string $token) {
        self::initKey();

        // séparer les 3 parties du token 
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$base64Header, $base64Payload,$base64Signature ] = $parts; 

        // recrée la signature pour vérification 
        $signature = hash_hmac(
            'sha256',
            $base64Header.'.'. $base64Payload,
            self::$key,
            true

        );
        // Vérifier la signature 
        if (!hash_equals(self::base64url_decode($base64Signature), $signature)) return false ;

        // decode le payload 
        $payload = json_decode(self::base64url_decode($base64Payload), true);

        if (isset($payload['exp'])&& $payload['exp'] < time()) return false;

        return $payload;
    }
}
