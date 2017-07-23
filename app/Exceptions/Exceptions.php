<?php

namespace App\Exceptions;

class Exceptions
{
    static public function oauthException()
    {
        return new Exception('OAuthException', 401);
    }

    static public function parameterException()
    {
        return new Exception('InvalidParameterException', 200);
    }

    static public function unauthenticatedException()
    {
        return new Exception('UnauthenticatedException', 401, 'Unauthenticated');
    }

    static public function invalidConfirmationException()
    {
        return new Exception('InvalidConfirmationCodeException', 200,
            'invalid confirmation code');
    }

    static public function invalidTokenException()
    {
        return new Exception('InvalidTokenException', 200, 'Invalid Token');
    }

    static public function nullException()
    {
        return new Exception('NullException', 200);
    }

    static public function notFoundException()
    {
        return new Exception('NotFoundException', 200, 'not found');
    }

    static public function invalidCredentialException()
    {
        return new Exception('InvalidCredentialException', 200);
    }

    static public function unconfirmedAccountException()
    {
        return new Exception('UnconfirmedAccountException', 200,
            'account has not been confirmed');
    }

    static public function invalidRequestException()
    {
        return new Exception('InvalidRequestException', 200);
    }

}