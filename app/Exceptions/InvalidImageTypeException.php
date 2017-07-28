<?php

namespace App\Exceptions;

class InvalidImageTypeException extends CustomException
{
    function __toString()
    {
        return 'InvalidImageTypeException';
    }
}