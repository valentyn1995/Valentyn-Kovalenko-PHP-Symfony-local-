<?php

namespace App\Services;

class TokenService
{
    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        
        return $token;
    }
}
