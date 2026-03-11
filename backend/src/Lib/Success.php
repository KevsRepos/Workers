<?php declare(strict_types=1);

namespace App\Lib;

final class Success {
    public function __construct(private mixed $response = "", private array $data = [], private int $code = 200)
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

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array{message: string, data: array}
     */
    public function getResponse(): array
    {
        return [
            'message' => $this->response,
            'data' => $this->data
        ];
    }
}