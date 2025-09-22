<?php

namespace Tests\Feature\Api;

use App\Models\Fornecedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FornecedorApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cria_fornecedor_com_sucesso(): void
    {
        $payload = [
            'nome' => 'Acme Ltda',
            'cnpj' => '11.222.333/0001-81',
            'email' => 'contato@acme.com',
        ];

        $res = $this->postJson('/api/fornecedores', $payload);

        $res->assertCreated()
            ->assertJsonPath('data.nome', 'Acme Ltda')
            ->assertJsonPath('data.cnpj', '11222333000181');

        $this->assertDatabaseHas('fornecedores', [
            'cnpj' => '11222333000181',
            'nome' => 'Acme Ltda',
        ]);
    }

    /** @test */
    public function falha_validacao_cnpj_invalido_e_nome_curto(): void
    {
        $res = $this->postJson('/api/fornecedores', [
            'nome' => 'X',
            'cnpj' => '123',
        ]);

        $res->assertUnprocessable()
            ->assertJsonValidationErrors(['nome', 'cnpj']);
    }

    /** @test */
    public function lista_com_filtro_por_nome(): void
    {
        Fornecedor::create([
            'nome' => 'Alpha Industria',
            'cnpj' => '11222333000181',
            'email' => 'alpha@ex.com',
        ]);

        Fornecedor::create([
            'nome' => 'Beta Comercio',
            'cnpj' => '04252011000110',
            'email' => 'beta@ex.com',
        ]);

        $res = $this->getJson('/api/fornecedores?nome=Alpha&limit=10');

        $res->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nome', 'Alpha Industria');
    }
}
