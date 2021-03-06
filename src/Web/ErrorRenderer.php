<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Base\Config;
use Ep\Base\Contract\ErrorRendererInterface;
use Ep\Web\Contract\ErrorRendererInterface as WebErrorRendererInterface;
use Ep\Helper\Date;
use Ep\Kit\ErrorMessage;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Http\Method;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use ErrorException;
use Throwable;

final class ErrorRenderer implements ErrorRendererInterface
{
    public function __construct(
        private ContainerInterface $container,
        private Config $config,
        private ErrorMessage $errorMessage,
        private LoggerInterface $logger,
        private Aliases $aliases,
        private View $view
    ) {
        $this->view = $view
            ->withViewPath('@ep/views')
            ->withContext($this)
            ->withContextId('error');
    }

    /**
     * {@inheritDoc}
     */
    public function render(Throwable $t, mixed $request): string
    {
        if ($this->config->debug) {
            $this->log($t, $request);

            return $this->view->renderPartial('development', [
                'exception' => $t,
                'request' => $request,
                'errorName' => $t instanceof ErrorException ? $this->errorMessage->getErrorName($t->getSeverity()) : null
            ]);
        } else {
            if ($this->container->has(WebErrorRendererInterface::class)) {
                return $this->container
                    ->get(WebErrorRendererInterface::class)
                    ->render($t, $request);
            } else {
                $this->log($t, $request);

                return $this->view->renderPartial('production');
            }
        }
    }

    private function log(Throwable $t, ServerRequestInterface $request): void
    {
        $context = [
            'category' => get_class($t),
            'host' => $request->getUri()->getHost(),
            'path' => $request->getRequestTarget(),
            'method' => $request->getMethod()
        ];

        if ($request->getMethod() === Method::POST) {
            $context['post'] = $request->getBody()->getContents() ?: $request->getParsedBody();
        }

        $this->logger->error($this->errorMessage->getMessage($t), $context);
    }

    public function renderPreviousException(Throwable $t): string
    {
        if (($previous = $t->getPrevious()) !== null) {
            return $this->view->renderPartial('_previous', ['exception' => $previous]);
        } else {
            return '';
        }
    }

    public function renderCallStackItem(?string $file, ?int $line, ?string $class, ?string $method, array $args, int $index): string
    {
        $lines = [];
        $begin = $end = 0;
        if ($file !== null && $line !== null) {
            $line--;
            $lines = @file($file);
            if ($line < 0 || $lines === false || ($lineCount = count($lines)) < $line) {
                return '';
            }
            $half = (int) (($index === 1 ? 19 : 13) / 2);
            $begin = $line - $half > 0 ? $line - $half : 0;
            $end = $line + $half < $lineCount ? $line + $half : $lineCount - 1;
        }

        return $this->view->renderPartial('_stack', compact('file', 'line', 'class', 'method', 'index', 'lines', 'begin', 'end', 'args'));
    }

    public function renderRequest(ServerRequestInterface $request): string
    {
        $output = $request->getMethod() . ' ' . $request->getUri() . "\n";
        foreach ($request->getHeaders() as $name => $values) {
            if ($name === 'Host') {
                continue;
            }
            foreach ($values as $value) {
                $output .= "{$name}: {$value}\n";
            }
        }
        $output .= "\n" . $request->getBody() . "\n\n";

        return '<pre>' . $this->htmlEncode(rtrim($output, "\n")) . '</pre>';
    }

    public function isVendorFile(?string $file): bool
    {
        return $file === null || str_starts_with($file, $this->aliases->get('@vendor'));
    }

    public function htmlEncode(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    public function argumentsToString(array $args): string
    {
        $isAssoc = $args !== array_values($args);

        foreach ($args as $key => $value) {
            if (is_object($value)) {
                $args[$key] = '<span class="title">' . $this->htmlEncode(get_class($value)) . '</span>';
            } elseif (is_bool($value)) {
                $args[$key] = '<span class="keyword">' . ($value ? 'true' : 'false') . '</span>';
            } elseif (is_string($value)) {
                $args[$key] = '<span class="string">\'' . $this->htmlEncode($value) . '\'</span>';
            } elseif (is_array($value)) {
                $args[$key] = '[' . $this->argumentsToString($value) . ']';
            } elseif ($value === null) {
                $args[$key] = '<span class="keyword">null</span>';
            } elseif (is_resource($value)) {
                $args[$key] = '<span class="keyword">resource</span>';
            } else {
                $args[$key] = '<span class="number">' . $value . '</span>';
            }

            if (is_string($key)) {
                $args[$key] = '<span class="string">\'' . $this->htmlEncode($key) . "'</span> => {$args[$key]}";
            } elseif ($isAssoc) {
                $args[$key] = "<span class=\"number\">{$key}</span> => {$args[$key]}";
            }
        }

        ksort($args);
        return implode(', ', $args);
    }

    public function getServerInfo(ServerRequestInterface $request): array
    {
        return [
            'Now' => Date::fromUnix(),
            'Server' => $request->getServerParams()['SERVER_SOFTWARE'] ?? 'Unknown',
            'PHP Version' => phpversion(),
            'Time Zone' => @ini_get('date.timezone') ?: 'Unknown',
            'Timeout' => @ini_get('max_execution_time') ?: 'Unknown',
            'Post Max Size' => @ini_get('post_max_size') ?: 'Unknown',
            'Upload Max Filesize' => @ini_get('upload_max_filesize') ?: 'Unknown',
        ];
    }
}
