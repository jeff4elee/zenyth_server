<?php

namespace App\Exceptions;

class RepositoryException extends CustomException
{
    public function __toString()
    {
        return 'RepositoryException';
    }
}