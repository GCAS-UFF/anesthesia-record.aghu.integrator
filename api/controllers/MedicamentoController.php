<?php

class MedicamentoController extends BaseController
{
    private MedicamentoRepository $repository;

    public function __construct()
    {
        $this->repository = new MedicamentoRepository();
    }

    public function listar(): void
    {
        $medicamentos = $this->repository->listar();

        $this->ok([
            'medicamentos' => $medicamentos
        ]);
    }
}