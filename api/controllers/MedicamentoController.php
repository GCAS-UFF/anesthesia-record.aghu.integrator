<?php

class MedicamentoController extends BaseController
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