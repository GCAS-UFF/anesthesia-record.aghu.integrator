<?php

class ldapService
{
    public function validar(string $login, string $senha): bool
    {
        $ldap = ldap_connect("ldap://servidor-ad");

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

        return @ldap_bind(
            $ldap,
            $login,
            $senha
        );
    }
}