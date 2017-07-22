<?php

namespace App\Exceptions;

class Exception
{

    public $message;
    public $type;
    public $statusCode;

    public function __construct($type, $statusCode, $message = null) {
        $this->message = $message;
        $this->type = $type;
        $this->statusCode = $statusCode;
    }

    public function getType() {
        return $this->type;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }

    public function getMessage() {
        return $this->message;
    }

}