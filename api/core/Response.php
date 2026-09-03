<?php

class response
{
    /**
     * Retorna uma resposta JSON.
     *
     * @param mixed $data
     * @param int $statusCode
     * @return void
     */
    public static function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_PRETTY_PRINT
        );

        exit;
    }

    /**
     * Retorna erro.
     *
     * @param string $message
     * @param int $statusCode
     * @return void
     */
    public static function error(string $message, int $statusCode = 500): void
    {
        self::json([
            'message' => $message
        ], $statusCode);
    }

    /**
     * Retorna recurso não encontrado.
     */
    public static function notFound(): void
    {
        self::error('Recurso não encontrado.', 404);
    }

    /**
     * Retorna requisição inválida.
     */
    public static function badRequest(string $message = 'Requisição inválida.'): void
    {
        self::error($message, 400);
    }

    /**
     * Retorna acesso não autorizado.
     */
    public static function unauthorized(string $message = 'Não autorizado.'): void
    {
        self::error($message, 401);
    }

	public static function noContent(): void
	{
		http_response_code(204);
		exit;
	}
}