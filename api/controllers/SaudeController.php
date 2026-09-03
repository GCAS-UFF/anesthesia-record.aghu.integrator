<?php

class saudeController extends baseController
{
    private saudeRepository $repository;

    public function __construct()
    {
        $this->repository = new saudeRepository();
    }

    public function verificar(): void
    {
        $this->ok([
            'online' => $this->repository->verificar()
        ]);
    }
}