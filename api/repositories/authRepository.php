<?php

class authRepository extends baseRepository
{
    public function buscarPorLogin(string $login): ?array
    {
        return $this->fetchOne("
            SELECT
                id,
                nome,
                login,
                especialidade,
                matricula,
                email,
                setor
            FROM aghu_stg.profissionais
            WHERE login = :login
        ", [
            ':login' => $login
        ]);
    }
}
