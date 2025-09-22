<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexFornecedorRequest;
use App\Http\Requests\StoreFornecedorRequest;
use App\Http\Resources\FornecedorResource;
use App\Services\Contracts\FornecedorServiceInterface;

class FornecedorController extends Controller
{
    public function __construct(private readonly FornecedorServiceInterface $service) {}

    public function index(IndexFornecedorRequest $request)
    {
        $data = $request->validated();
        $list = $this->service->list($data['nome'] ?? null, $data['limit'] ?? 50);

        return FornecedorResource::collection($list);
    }

    public function store(StoreFornecedorRequest $request)
    {
        $fornecedor = $this->service->create($request->validated());

        return (new FornecedorResource($fornecedor))
            ->response()
            ->setStatusCode(201);
    }
}
