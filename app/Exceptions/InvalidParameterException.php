<?php


namespace App\Exceptions;


class InvalidParameterException extends \Exception
{
    public function __toString()
    {
        return 'InvalidParameterException';
    }

}