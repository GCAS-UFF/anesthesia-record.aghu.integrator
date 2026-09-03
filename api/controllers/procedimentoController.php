<?php

class procedimentoController extends baseController
{
    private procedimentoRepository $repository;

    public function __construct()
    {
        $this->repository = new procedimentoRepository();
    }

    public function listar(): void
    {
        $procedimentos = $this->repository->listar();

        $this->ok([
            'procedimentos' => $procedimentos
        ]);
    }
}