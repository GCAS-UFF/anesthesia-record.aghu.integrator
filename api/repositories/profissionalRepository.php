<?php

class profissionalRepository extends baseRepository
{
    public function listar(): array
    {
        $stmt = $this->db->query("
            SELECT nome, login, matricula, especialidade, email, setor, ativo
            FROM aghu_stg.PROFISSIONAIS
        ");

        return $stmt->fetchAll();
    }
}
