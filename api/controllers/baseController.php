<?php

abstract class baseController
{
    protected function ok($data): void
    {
        Response::json($data);
    }

    protected function badRequest(string $message): void
    {
        Response::badRequest($message);
    }

    protected function unauthorized(string $message = 'Não autorizado.'): void
    {
        Response::unauthorized($message);
    }

    protected function notFound(): void
    {
        Response::notFound();
    }
}