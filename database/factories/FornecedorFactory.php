<?php

namespace Database\Factories;

use App\Models\Fornecedor;
use Illuminate\Database\Eloquent\Factories\Factory;

class FornecedorFactory extends Factory
{
    protected $model = Fornecedor::class;

    public function definition(): array
    {
        return [
            'nome' => $this->faker->company(),
            'cnpj' => $this->faker->unique()->numerify('########0001##'),
            'email' => $this->faker->optional()->companyEmail(),
        ];
    }
}
