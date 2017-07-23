<?php

namespace App\Exceptions;

class Exceptions
{
    static public function oauthException($message)
    {
        throw new OAuthException($message, 401);
    }

    static public function parameterException($message)
    {
        throw new InvalidParameterException($message, 200);
    }

    static public function unauthenticatedException($message)
    {
        return new UnauthenticatedException($message, 401);
    }

    static public function invalidConfirmationException($message)
    {
        return new InvalidConfirmationCodeException($message, 200);
    }

    static public function invalidTokenException($message)
    {
        return new InvalidTokenException($message, 200);
    }

    static public function nullException($message)
    {
        return new NullException($message, 200);
    }

    static public function notFoundException($message)
    {
        return new NotFoundException($message, 200);
    }

    static public function invalidCredentialException($message)
    {
        return new InvalidCredentialException($message, 200);
    }

    static public function unconfirmedAccountException($message)
    {
        return new UnconfirmedAccountException($message, 200);
    }

    static public function invalidRequestException($message)
    {
        return new InvalidRequestException($message, 200);
    }

}