<?php


namespace App\Exceptions;


class InvalidParameterException extends CustomException
{
    public function __toString()
    {
        return 'InvalidParameterException';
    }

}