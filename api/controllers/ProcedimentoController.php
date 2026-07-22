<?php

class ProcedimentoController extends BaseController
{
    private ProcedimentoRepository $repository;

    public function __construct()
    {
        $this->repository = new ProcedimentoRepository();
    }

    public function listar(): void
    {
        $procedimentos = $this->repository->listar();

        $this->ok([
            'procedimentos' => $procedimentos
        ]);
    }
}