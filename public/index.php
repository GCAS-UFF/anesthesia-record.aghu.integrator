<?php

date_default_timezone_set('America/Sao_Paulo');

header('Content-Type: application/json; charset=utf-8');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../api/bootstrap.php';

$router = new Router();

$router->post('/auth', [AuthController::class, 'login']);

$router->get('/cirurgias', [CirurgiaController::class, 'listar']);
$router->get('/cirurgias/por-ids', [CirurgiaController::class, 'listarPorIds']);
$router->get('/cirurgias/{idPaciente}/{idCirurgia}', [CirurgiaController::class, 'buscar']);

$router->get('/medicamentos', [MedicamentoController::class, 'listar']);
$router->get('/profissionais', [ProfissionalController::class, 'listar']);
$router->get('/procedimentos', [ProcedimentoController::class, 'listar']);

$router->get('/saude', [SaudeController::class, 'verificar']);

$router->dispatch();