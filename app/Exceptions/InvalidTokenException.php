<?php
/**
 * Created by IntelliJ IDEA.
 * User: hnguyen0428
 * Date: 7/23/17
 * Time: 12:13 AM
 */

namespace App\Exceptions;


class InvalidTokenException extends \Exception
{
    public function __toString()
    {
        return 'InvalidTokenException';
    }
}