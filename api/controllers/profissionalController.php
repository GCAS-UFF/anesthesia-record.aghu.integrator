<?php

class profissionalController extends baseController
{
    private profissionalRepository $repository;

    public function __construct()
    {
        $this->repository = new profissionalRepository();
    }

    /**
     * GET /profissionais
     */
    public function listar(): void
    {
        $profissionais = $this->repository->listar();

        $this->ok([
            'profissionais' => $profissionais
        ]);
    }
}