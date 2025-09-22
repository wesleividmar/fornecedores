<?php

use App\Http\Controllers\Api\FornecedorController;
use Illuminate\Support\Facades\Route;

Route::get('/fornecedores', [FornecedorController::class, 'index']);
Route::post('/fornecedores', [FornecedorController::class, 'store']);
