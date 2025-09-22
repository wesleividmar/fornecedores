<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Fornecedor;
use Illuminate\Support\Collection;

interface FornecedorServiceInterface
{
    public function create(array $attributes): Fornecedor;

    public function list(?string $nameFilter = null, int $limit = 50): Collection;
}
