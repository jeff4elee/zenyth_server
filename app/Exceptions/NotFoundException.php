<?php
/**
 * Created by IntelliJ IDEA.
 * User: hnguyen0428
 * Date: 7/23/17
 * Time: 12:14 AM
 */

namespace App\Exceptions;


class NotFoundException extends CustomException
{
    public function __toString()
    {
        return 'NotFoundException';
    }
}