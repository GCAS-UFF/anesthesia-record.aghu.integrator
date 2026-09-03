<?php

class authService
{
    private authRepository $repository;
    private ldapService $ldap;

    public function __construct()
    {
        $this->repository = new authRepository();
        $this->ldap = new ldapService();
    }

    public function autenticar(string $login, string $senha): bool
    {
        // Quando for autenticado via LDAP, chamar esse método e implementar logica no service de LDAP
        // return $this->ldap->validar($login, $senha);
        return true;
    }

    public function buscarUsuario(string $login): ?array
    {
        return $this->repository->buscarPorLogin($login);
    }
}