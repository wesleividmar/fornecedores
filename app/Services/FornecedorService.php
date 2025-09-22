<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Fornecedor;
use App\Services\Contracts\FornecedorServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FornecedorService implements FornecedorServiceInterface
{
    public function create(array $attributes): Fornecedor
    {
        return DB::transaction(function () use ($attributes) {
            $payload = [
                'nome' => trim($attributes['nome']),
                'cnpj' => $this->digitsOnly($attributes['cnpj']),
                'email' => $attributes['email'] ?? null,
            ];

            /** @var Fornecedor $fornecedor */
            $fornecedor = Fornecedor::create($payload);

            return $fornecedor;
        });
    }

    public function list(?string $nameFilter = null, int $limit = 50): Collection
    {
        return Fornecedor::query()
            ->when($nameFilter, fn ($q) => $q->where('nome', 'like', "%{$nameFilter}%"))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    private function digitsOnly(?string $v): string
    {
        return preg_replace('/\D+/', '', (string) $v);
    }
}
