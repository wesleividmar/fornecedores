<?php

namespace Database\Seeders;

use App\Models\Fornecedor;
use Illuminate\Database\Seeder;

class FornecedorSeeder extends Seeder
{
    public function run(): void
    {

        Fornecedor::query()->create([
            'nome' => 'Acme Ltda',
            'cnpj' => '11222333000181',
            'email' => 'contato@acme.com',
        ]);

        Fornecedor::query()->create([
            'nome' => 'Beta Comercio',
            'cnpj' => '04252011000110',
            'email' => 'beta@ex.com',
        ]);

        Fornecedor::factory(8)->create();
    }
}
