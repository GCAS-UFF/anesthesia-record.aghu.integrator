<?php

class saudeRepository extends baseRepository
{
    public function verificar(): bool
    {
        try {

            $stmt = $this->db->query("SELECT 1");

            return $stmt !== false;

        } catch (Exception $e) {

            return false;

        }
    }
}