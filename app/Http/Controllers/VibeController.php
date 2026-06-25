<?php

namespace App\Http\Controllers;

use App\Ai\Agents\VibePhpRuntime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VibeController extends Controller
{
    /**
     * Serve any incoming request by handing the matching PHP script to the
     * Vibe PHP runtime and returning the HTTP response it "produces".
     */
    public function __invoke(Request $request): Response
    {
        $script = $this->resolveScript($request->path());

        if ($script === null) {
            return response('Vibe PHP: No script found for this URL, and no index.php to route it.', 404);
        }

        $result = (new VibePhpRuntime)->prompt(
            $this->buildPrompt($request, $script),
            model: config('vibe.model'),
        );

        return $this->toHttpResponse($result['status'], $result['headers'], $result['body']);
    }

    /**
     * Resolve the request path to a PHP file within the docroot, falling back
     * to index.php so it can act as a front controller. Returns the script's
     * path relative to the docroot, or null when nothing can handle it.
     */
    protected function resolveScript(string $path): ?string
    {
        $docroot = (string) config('vibe.docroot');
        $path = trim($path, '/');

        $candidates = $path === ''
            ? ['index.php']
            : [$path, "{$path}.php", "{$path}/index.php", 'index.php'];

        foreach ($candidates as $candidate) {
            if (is_file($docroot.DIRECTORY_SEPARATOR.$candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Build the prompt describing the HTTP request and the entry script source.
     */
    protected function buildPrompt(Request $request, string $script): string
    {
        $docroot = (string) config('vibe.docroot');
        $source = file_get_contents($docroot.DIRECTORY_SEPARATOR.$script);

        $context = [
            'method' => $request->method(),
            'uri' => $request->getRequestUri(),
            'path' => '/'.ltrim($request->path(), '/'),
            'query' => $request->query(),
            'headers' => collect($request->headers->all())
                ->map(fn ($values) => implode(', ', $values))
                ->all(),
            'cookies' => $request->cookies->all(),
            'body' => $request->getContent(),
        ];

        return 'Execute this PHP request and return the HTTP response.'.PHP_EOL.PHP_EOL
            .'## HTTP request'.PHP_EOL
            .'```json'.PHP_EOL
            .json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
            .'```'.PHP_EOL.PHP_EOL
            ."## Entry script: {$script}".PHP_EOL
            .'```php'.PHP_EOL
            .$source.PHP_EOL
            .'```';
    }

    /**
     * Map the runtime's structured output onto a real HTTP response.
     *
     * @param  array<int, array{name: string, value: string}>  $headers
     */
    protected function toHttpResponse(int $status, array $headers, string $body): Response
    {
        $response = response($body, $status);

        $hasContentType = false;

        foreach ($headers as $header) {
            $name = $header['name'] ?? null;
            $value = $header['value'] ?? null;

            if ($name === null || $value === null) {
                continue;
            }

            // Content-Length is recomputed by the framework; ignore the imagined one.
            if (strcasecmp($name, 'Content-Length') === 0) {
                continue;
            }

            $hasContentType = $hasContentType || strcasecmp($name, 'Content-Type') === 0;

            $response->header($name, $value);
        }

        if (! $hasContentType) {
            $response->header('Content-Type', 'text/html; charset=UTF-8');
        }

        return $response;
    }
}
