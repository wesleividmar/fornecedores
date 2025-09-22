<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\Cnpj;

class StoreFornecedorRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'cnpj' => preg_replace('/\D+/', '', (string) $this->input('cnpj')),
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'min:3'],
            'cnpj' => ['required', new Cnpj, 'unique:fornecedores,cnpj'],
            'email' => ['nullable', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'Nome é obrigatório.',
            'nome.min' => 'Nome muito curto.',
            'cnpj.required' => 'CNPJ é obrigatório.',
            'cnpj.unique' => 'CNPJ já cadastrado.',
            'email.email' => 'Email inválido.',
        ];
    }
}
