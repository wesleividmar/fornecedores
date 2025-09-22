<?php

declare(strict_types=1);

namespace App\Http\Requests;

class IndexFornecedorRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nome' => $this->query('nome', null),
            'limit' => (int) ($this->query('limit', 50)),
        ]);
    }

    public function rules(): array
    {
        return [
            'nome' => ['nullable', 'string', 'min:1', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ];
    }
}
