<?php

class ProfissionalController extends BaseController
{
    private ProfissionalRepository $repository;

    public function __construct()
    {
        $this->repository = new ProfissionalRepository();
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