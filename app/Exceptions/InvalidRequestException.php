<?php
/**
 * Created by IntelliJ IDEA.
 * User: hnguyen0428
 * Date: 7/23/17
 * Time: 12:16 AM
 */

namespace App\Exceptions;


class InvalidRequestException extends \Exception
{
    public function __toString()
    {
        return 'InvalidRequestException';
    }
}