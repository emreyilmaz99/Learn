<?php

namespace App\Core\Class;

class ServiceResponse
{
    private int $statusCode;
    private bool $status;
    private string $message;
    private $data;

    public function __construct(int $statusCode, bool $status, string $message, $data = null)
    {
        $this->statusCode = $statusCode;
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
    }

    public function getStatusCode() : int
    {
        return $this->statusCode;
        
    }
    public function isSuccess() : bool
    {
        return $this->status;
    }
    public function getMessage() : string
    {
        return $this->message;
    }
    public function getData()
    {
        return $this->data;
    }

}