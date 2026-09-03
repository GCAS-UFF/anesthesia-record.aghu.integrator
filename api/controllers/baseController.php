<?php

abstract class baseController
{
    protected function ok($data): void
    {
        response::json($data);
    }

    protected function badRequest(string $message): void
    {
        response::badRequest($message);
    }

    protected function unauthorized(string $message = 'Não autorizado.'): void
    {
        response::unauthorized($message);
    }

    protected function notFound(): void
    {
        response::notFound();
    }
}