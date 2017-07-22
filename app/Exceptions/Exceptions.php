<?php

namespace App\Exceptions;

class Exceptions
{
    static public function oauthException()
    {
        return new Exception('OAuthException', 190);
    }

    static public function parameterException()
    {
        return new Exception('InvalidParameterException', 100);
    }

    static public function unauthenticatedException()
    {
        return new Exception('UnauthenticatedException', 401);
    }

    static public function invalidConfirmationException()
    {
        return new Exception('InvalidConfirmationCodeException', 200,
            'Invalid confirmation code');
    }

    static public function nullException()
    {
        return new Exception('NullException', 200);
    }

    static public function notFoundException()
    {
        return new Exception('NotFoundException', 404);
    }

}