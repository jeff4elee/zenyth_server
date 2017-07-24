<?php
/**
 * Created by IntelliJ IDEA.
 * User: hnguyen0428
 * Date: 7/23/17
 * Time: 12:08 AM
 */

namespace App\Exceptions;


class OAuthException extends CustomException
{
    public function __toString()
    {
        return 'OAuthException';
    }
}