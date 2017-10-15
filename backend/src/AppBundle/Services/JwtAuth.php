<?php

namespace AppBundle\Services;

use Firebase\JWT\JWT;

class JwtAuth
{
    public $key;

    public function __construct()
    {
        $this->key = 'SecretKey...123...';
    }

    public function signUp($user, $getHash = null)
    {
        $token = [
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'surname' => $user->getSurname(),
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60),
        ];

        if ($getHash === true) {
            $data = $token;
        } else {
            $data = JWT::encode($token, $this->key, 'HS256');
        }

        return $data;
    }

    public function checkToken($jwt, $getIdentity = false)
    {
        $auth = false;
        try {
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }
        if (isset($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        }
        if ($getIdentity === false) {
            return $auth;
        } else {
            return $decoded;
        }
    }
}
