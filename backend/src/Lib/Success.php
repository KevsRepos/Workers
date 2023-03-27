<?php declare(strict_types=1);

namespace App\Lib;

final class Success {
    public function __construct(private mixed $response = "", private int $code = 200)
    {
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getMessage()
    {
        return $this->response;
    }
}