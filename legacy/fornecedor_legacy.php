<?php

/**
 * Script legado (exemplo para migração)
 * ------------------------------------
 * - PHP procedural com mysqli
 * - Sem PSR-12, sem camadas, validação mínima
 * - Vulnerável a SQL Injection (intencional para ilustração)
 *
 * Tabela esperada:
 * CREATE TABLE fornecedores (
 *   id INT AUTO_INCREMENT PRIMARY KEY,
 *   nome VARCHAR(255) NOT NULL,
 *   cnpj VARCHAR(14) NOT NULL,
 *   email VARCHAR(255) NULL,
 *   criado_em DATETIME NOT NULL
 * );
 * CREATE UNIQUE INDEX ux_fornecedores_cnpj ON fornecedores(cnpj);
 */
$mysqli = new mysqli('localhost', 'root', '', 'legacy_db');

if ($mysqli->connect_error) {
    exit('erro db');
}

$action = $_GET['action'] ?? 'list';

function onlyDigits($s)
{
    return preg_replace('/\D+/', '', $s);
}

if ($action === 'create') {
    $nome = $_POST['nome'] ?? '';
    $cnpj = onlyDigits($_POST['cnpj'] ?? '');
    $email = $_POST['email'] ?? '';

    if (strlen($nome) < 3) {
        exit('nome curto');
    }
    if (strlen($cnpj) != 14) {
        exit('cnpj invalido');
    }

    // Inserção direta (sem prepared statements) — legado e inseguro
    $sql = "INSERT INTO fornecedores (nome, cnpj, email, criado_em) VALUES ('$nome', '$cnpj', '$email', NOW())";
    if (! $mysqli->query($sql)) {
        exit('erro insert: '.$mysqli->error);
    }

    echo 'ok';
} else {
    // list (busca por nome, com LIKE concatenado — inseguro no legado)
    $q = $_GET['q'] ?? '';

    $sql = "SELECT id, nome, cnpj, email, criado_em
            FROM fornecedores
            WHERE nome LIKE '%$q%'
            ORDER BY criado_em DESC
            LIMIT 50";

    $res = $mysqli->query($sql);
    $data = [];

    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($data);
}
