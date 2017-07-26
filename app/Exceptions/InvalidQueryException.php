<?php
/**
 * Created by PhpStorm.
 * User: hnguyen0428
 * Date: 7/24/17
 * Time: 3:21 PM
 */

namespace App\Exceptions;


class InvalidQueryException extends \Exception
{
    public function __toString()
    {
        return 'InvalidQueryException';
    }
}