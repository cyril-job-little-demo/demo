<?php
namespace App;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response;
use Throwable;

class ErrorHandlerMiddleware
{
    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
        ?LoggerInterface $logger = null
    ): Response
    {
        $code = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
        if ($exception->getCode()) {
            $code = $exception->getCode();
        }

        $response = (new Response())->withStatus($code, $exception->getMessage());
        $response->getBody()->write(
            json_encode(['error' => $exception->getMessage()], JSON_UNESCAPED_UNICODE)
        );

        return $response->withHeader('Content-Type', 'application/json');
    }
}