<?php

class medicamentoRepository extends baseRepository
{
    public function listar(): array
    {
        return $this->fetchAll("
            SELECT
                id,
                codigo,
                descricao,
                unidade,
                tipo,
                ativo
            FROM aghu_stg.medicamentos
            WHERE ativo = true
            ORDER BY descricao
        ");
    }
}