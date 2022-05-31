<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\ErrorRendererInterface;
use ErrorException;
use Throwable;

final class ErrorHandler
{

    private function __construct(private ErrorRendererInterface $errorRenderer)
    {
    }

    public static function create(ErrorRendererInterface $errorRenderer): self
    {
        return new self($errorRenderer);
    }

    public function register(mixed $request): void
    {
        set_exception_handler(fn (Throwable $e) => $this->handleException($e, $request));
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleFatalError'], $request);
    }

    public function handleException(Throwable $t, mixed $request): void
    {
        $this->unregister();

        echo $this->errorRenderer->render($t, $request);

        exit(1);
    }

    public function handleError(int $severity, string $message, string $file, int $line): void
    {
        if (!(error_reporting() & $severity)) {
            return;
        }

        throw new ErrorException($message, $severity, $severity, $file, $line);
    }

    public function handleFatalError(mixed $request): void
    {
        $error = error_get_last();
        if ($error !== null && $this->isFatalError($error)) {
            $exception = new ErrorException(
                $error['message'],
                $error['type'],
                $error['type'],
                $error['file'],
                $error['line']
            );
            $this->handleException($exception, $request);
        }
    }

    private function isFatalError(array $error): bool
    {
        return in_array($error['type'] ?? null, [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING], true);
    }

    private function unregister(): void
    {
        restore_error_handler();
        restore_exception_handler();
    }
}
