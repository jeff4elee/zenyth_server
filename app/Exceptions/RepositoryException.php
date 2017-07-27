<?php

namespace App\Exceptions;

class RepositoryException extends \Exception
{
    public function __toString()
    {
        return 'RepositoryException';
    }
}