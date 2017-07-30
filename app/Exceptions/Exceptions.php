<?php

namespace App\Exceptions;

class Exceptions
{
    static public function invalidRequestException($message = "")
    {
        throw new InvalidRequestException($message, 1);
    }

    static public function invalidQueryException($message = "")
    {
        throw new InvalidQueryException($message, 2);
    }

    static public function unknownErrorException($message = "")
    {
        throw new UnknownErrorException($message, 3);
    }

    static public function repositoryException($message = "")
    {
        throw new RepositoryException($message, 4);
    }

    static public function invalidParameterException($message = "")
    {
        throw new InvalidParameterException($message, 100);
    }

    static public function oauthException($message = "")
    {
        throw new OAuthException($message, 200);
    }

    static public function unauthenticatedException($message = "")
    {
        throw new UnauthenticatedException($message, 201);
    }

    static public function invalidConfirmationCodeException($message = "")
    {
        throw new InvalidConfirmationCodeException($message, 202);
    }

    static public function invalidTokenException($message = "")
    {
        throw new InvalidTokenException($message, 203);
    }

    static public function invalidCredentialException($message = "")
    {
        throw new InvalidCredentialException($message, 204);
    }

    static public function unconfirmedAccountException($message = "")
    {
        throw new UnconfirmedAccountException($message, 205);
    }

    static public function invalidColumnException($message = "")
    {
        throw new InvalidColumnException($message, 206);
    }

    static public function nullException($message = "")
    {
        throw new NullException($message, 300);
    }

    static public function notFoundException($message = "")
    {
        throw new NotFoundException($message, 301);
    }

    static public function invalidImageTypeException($message = "")
    {
        throw new InvalidImageTypeException($message, 302);
    }
}