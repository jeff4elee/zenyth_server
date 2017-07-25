<?php
/**
 * Created by IntelliJ IDEA.
 * User: hnguyen0428
 * Date: 7/23/17
 * Time: 12:15 AM
 */

namespace App\Exceptions;


class UnconfirmedAccountException extends CustomException
{
    public function __toString()
    {
        return 'UnconfirmedAccountException';
    }
}