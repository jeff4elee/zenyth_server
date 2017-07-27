<?php

namespace App\Exceptions;


class InvalidColumnException extends CustomException
{
    public function __toString()
    {
        return 'InvalidColumnException';
    }
}