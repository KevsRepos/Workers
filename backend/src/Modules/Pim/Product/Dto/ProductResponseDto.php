<?php

namespace App\Modules\Pim\Product\Dto;

class ProductResponseDto extends PaginationResponseDto
{
    public array $data = [];

    public int $page = 1;

    public int $limit = 20;

    public int $total = 0;
}
