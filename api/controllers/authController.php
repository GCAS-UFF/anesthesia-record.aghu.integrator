<?php

class AuthController extends BaseController
{
    private authService $service;

    public function __construct()
    {
        $this->service = new authService();
    }

    public function login(): void
    {
        $body = json_decode(file_get_contents('php://input'), true);

        if (empty($body['login']) || empty($body['senha'])) {
            $this->badRequest('Login e senha são obrigatórios.');
            return;
        }

        $autenticado = $this->service->autenticar(
            $body['login'],
            $body['senha']
        );

        if (!$autenticado) {
            $this->unauthorized('Login ou senha inválidos.');
            return;
        }

        $usuario = $this->service->buscarUsuario(
            $body['login']
        );

        if ($usuario === null) {
            $this->notFound('Usuário não encontrado.');
            return;
        }

        $this->ok($usuario);
    }
}