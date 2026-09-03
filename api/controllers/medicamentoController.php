<?php

class medicamentoController extends baseController
{
    private medicamentoRepository $repository;

    public function __construct()
    {
        $this->repository = new medicamentoRepository();
    }

    public function listar(): void
    {
        $medicamentos = $this->repository->listar();

        $this->ok([
            'medicamentos' => $medicamentos
        ]);
    }
}