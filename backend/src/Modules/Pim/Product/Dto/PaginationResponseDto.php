<?php

namespace App\Modules\Pim\Product\Dto;

use Symfony\Component\Serializer\Attribute\Groups;

class PaginationResponseDto
{
    public array $data = [];

    public int $page = 1;

    public int $limit = 20;

    public int $total = 0;
}
