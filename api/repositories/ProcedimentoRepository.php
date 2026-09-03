<?php

class procedimentoRepository extends baseRepository
{
   public function listar(): array
    {
        return $this->fetchAll("
            SELECT
				id,
				codigo,
				descricao,
				cid
			FROM aghu_stg.procedimentos
			ORDER BY descricao;
        ");
    }
}