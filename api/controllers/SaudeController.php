<?php

class SaudeController extends BaseController
{
    private SaudeRepository $repository;

    public function __construct()
    {
        $this->repository = new SaudeRepository();
    }

    public function verificar(): void
    {
        $this->ok([
            'online' => $this->repository->verificar()
        ]);
    }
}