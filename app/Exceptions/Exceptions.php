<?php

namespace App\Exceptions;

class Exceptions
{
    static public function oauthException($message = "")
    {
        throw new OAuthException($message, 401);
    }

    static public function parameterException($message = "")
    {
        throw new InvalidParameterException($message, 200);
    }

    static public function unauthenticatedException($message = "")
    {
        throw new UnauthenticatedException($message, 401);
    }

    static public function invalidConfirmationException($message = "")
    {
        throw new InvalidConfirmationCodeException($message, 200);
    }

    static public function invalidTokenException($message = "")
    {
        throw new InvalidTokenException($message, 200);
    }

    static public function nullException($message = "")
    {
        throw new NullException($message, 200);
    }

    static public function notFoundException($message = "")
    {
        throw new NotFoundException($message, 200);
    }

    static public function invalidCredentialException($message = "")
    {
        throw new InvalidCredentialException($message, 200);
    }

    static public function unconfirmedAccountException($message = "")
    {
        throw new UnconfirmedAccountException($message, 200);
    }

    static public function invalidRequestException($message = "")
    {
        throw new InvalidRequestException($message, 200);
    }

}