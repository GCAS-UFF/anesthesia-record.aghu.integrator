<?php

class SaudeRepository extends BaseRepository
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