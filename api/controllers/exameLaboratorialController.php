<?php

class exameLaboratorialController extends baseController
{
    private exameLaboratorialRepository $repository;

    public function __construct()
    {
        $this->repository = new exameLaboratorialRepository();
    }

    
    public function ultimo(string $idPaciente): void
    {
        $idPaciente = trim(urldecode($idPaciente));

        if ($idPaciente === '' || strlen($idPaciente) > 50 || !preg_match('/^[A-Za-z0-9._-]+$/', $idPaciente)) {
            $this->badRequest('Identificador do paciente inválido.');
        }

        try {
            $resultado = $this->repository->buscarUltimoLiberado($idPaciente);
        } catch (PDOException $e) {
            error_log('[exames-laboratoriais] Falha ao consultar o banco do AGHU (SQLSTATE ' . $e->getCode() . ').');
            response::error('Falha ao consultar os exames laboratoriais no AGHU.', 500);
        }

        if ($resultado === null) {
            response::error('Paciente não encontrado.', 404);
        }

        $this->ok($resultado);
    }
}
