<?php

class cirurgiaController extends baseController
{
    private cirurgiaRepository $repository;

    public function __construct()
    {
        $this->repository = new cirurgiaRepository();
    }

    public function listar(): void
    {
        $data = $_GET['data'] ?? null;
        $status = $_GET['status'] ?? null;
		$termo = $_GET['termo'] ?? null;

        $page = isset($_GET['page'])
            ? max(1, (int) $_GET['page'])
            : 1;

        $pageSize = isset($_GET['pageSize'])
            ? max(1, (int) $_GET['pageSize'])
            : 10;

        $resultado = $this->repository->listar(
            $data,
			$termo,
            $status,
            $page,
            $pageSize
        );

        $this->ok($resultado);
    }

	public function listarPorIds(): void
	{
		$ids = !empty($_GET['ids'])
			? array_map('intval', explode(',', $_GET['ids']))
			: [];

		$status = $_GET['status'] ?? null;
		$termo = $_GET['termo'] ?? null;

		$page = isset($_GET['page'])
			? max(1, (int)$_GET['page'])
			: 1;

		$pageSize = isset($_GET['pageSize'])
			? max(1, (int)$_GET['pageSize'])
			: 10;

		$resultado = $this->repository->listarPorIds(
			$ids,
			$termo,
			$status,
			$page,
			$pageSize
		);

		$this->ok($resultado);
	}
    public function buscar(string $idPaciente, int $idCirurgia): void
    {
        $resultado = $this->repository->buscar($idPaciente, $idCirurgia);

        if ($resultado === null) {
            $this->notFound();
        }

        $this->ok($resultado);
    }
}