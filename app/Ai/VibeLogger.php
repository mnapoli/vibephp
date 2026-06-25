<?php

namespace App\Ai;

use Laravel\Ai\Events\ToolInvoked;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;

/**
 * Pretty per-request logging for the Vibe runtime: a start line, a line per
 * tool call, and a summary with timing, token usage and an estimated cost.
 *
 * Lines are written to stderr (so they appear live under `php artisan serve`
 * and `php artisan vibe`) and, optionally, appended to a log file. This is
 * registered as a singleton so the request and the ToolInvoked listener share
 * the same instance and tool counter.
 */
class VibeLogger
{
    protected int $toolCount = 0;

    /**
     * Begin logging a request.
     */
    public function start(string $method, string $path, string $script): void
    {
        $this->toolCount = 0;

        $this->write(
            $this->c('1;35', '▸ ').$this->c('1', "{$method} {$path}").$this->c('2', "  ({$script})"),
            "▸ {$method} {$path}  ({$script})",
        );
    }

    /**
     * Log a single tool invocation as it happens.
     */
    public function tool(ToolInvoked $event): void
    {
        $this->toolCount++;

        $name = method_exists($event->tool, 'name')
            ? $event->tool->name()
            : class_basename($event->tool);

        $args = collect($event->arguments)
            ->map(fn ($value, $key) => $key.'='.json_encode($value))
            ->implode(' ');

        $size = is_string($event->result) ? strlen($event->result) : strlen((string) json_encode($event->result));

        $this->write(
            $this->c('2', '  🔧 ').$this->c('36', $name).$this->c('2', "  {$args}  → ").$this->humanBytes($size),
            "  🔧 {$name}  {$args}  → ".$this->humanBytes($size),
        );
    }

    /**
     * Log the request summary once the runtime has produced a response.
     */
    public function done(string $method, string $path, int $status, float $seconds, ?Usage $usage = null, ?Meta $meta = null): void
    {
        $statusColor = match (intdiv($status, 100)) {
            2 => '32',
            3 => '36',
            4 => '33',
            default => '31',
        };

        $tokens = $usage ? $this->formatTokens($usage) : $this->c('2', 'no model');
        $cost = $usage ? $this->formatCost($usage, $meta?->model) : '';
        $tools = $this->toolCount > 0 ? $this->c('2', ' · ').$this->c('35', "🔧{$this->toolCount}") : '';
        $model = $meta?->model ? $this->c('2', ' · ').$this->c('2', $meta->model) : '';

        $colored = $this->c('2', '◂ ')
            .$this->c($statusColor, (string) $status)
            .$this->c('2', " {$method} {$path}")
            .$this->c('2', ' · ').$this->c('1', $this->humanTime($seconds))
            .$model
            .$this->c('2', ' · ').$tokens
            .($cost !== '' ? $this->c('2', ' · ').$cost : '')
            .$tools;

        $plainTools = $this->toolCount > 0 ? " · 🔧{$this->toolCount}" : '';
        $plain = "◂ {$status} {$method} {$path} · {$this->humanTime($seconds)}"
            .($meta?->model ? " · {$meta->model}" : '')
            .' · '.$this->plainTokens($usage)
            .($usage ? ' · '.$this->plainCost($usage, $meta?->model) : '')
            .$plainTools;

        $this->write($colored, $plain);
    }

    /**
     * Estimate the dollar cost of a response from the configured price table.
     */
    protected function cost(Usage $usage, ?string $model): ?float
    {
        $prices = $this->pricesFor($model);

        if (! $prices) {
            return null;
        }

        $cachedInput = $usage->cacheReadInputTokens;
        $freshInput = max($usage->promptTokens - $cachedInput, 0) + $usage->cacheWriteInputTokens;
        $output = $usage->completionTokens;

        return $freshInput / 1_000_000 * $prices['input']
            + $cachedInput / 1_000_000 * ($prices['cached_input'] ?? $prices['input'])
            + $output / 1_000_000 * $prices['output'];
    }

    /**
     * Resolve the price table for a model, matching dated snapshots like
     * "gpt-5.4-2026-03-05" against the longest configured prefix ("gpt-5.4").
     *
     * @return array{input: float, output: float, cached_input?: float}|null
     */
    protected function pricesFor(?string $model): ?array
    {
        if ($model === null) {
            return null;
        }

        $table = config('vibe.pricing', []);

        if (isset($table[$model])) {
            return $table[$model];
        }

        $match = collect($table)
            ->filter(fn ($prices, $key) => str_starts_with($model, $key))
            ->sortKeys()
            ->keys()
            ->sortByDesc(fn ($key) => strlen($key))
            ->first();

        return $match ? $table[$match] : null;
    }

    protected function formatTokens(Usage $usage): string
    {
        $cached = $usage->cacheReadInputTokens > 0
            ? $this->c('2', ' ('.number_format($usage->cacheReadInputTokens).' cached)')
            : '';

        return $this->c('36', number_format($usage->promptTokens).'↑')
            .' '.$this->c('35', number_format($usage->completionTokens).'↓')
            .$this->c('2', ' tok').$cached;
    }

    protected function plainTokens(?Usage $usage): string
    {
        if (! $usage) {
            return 'no model';
        }

        return number_format($usage->promptTokens).'↑ '.number_format($usage->completionTokens).'↓ tok';
    }

    protected function formatCost(Usage $usage, ?string $model): string
    {
        $cost = $this->cost($usage, $model);

        return $cost === null
            ? $this->c('2', '~$?')
            : $this->c('32', '~$'.number_format($cost, 4));
    }

    protected function plainCost(Usage $usage, ?string $model): string
    {
        $cost = $this->cost($usage, $model);

        return $cost === null ? '~$?' : '~$'.number_format($cost, 4);
    }

    protected function humanTime(float $seconds): string
    {
        return $seconds < 1
            ? round($seconds * 1000).'ms'
            : number_format($seconds, 2).'s';
    }

    protected function humanBytes(int $bytes): string
    {
        return $bytes < 1024
            ? "{$bytes} B"
            : number_format($bytes / 1024, 1).' KB';
    }

    /**
     * Wrap text in an ANSI color code (no-op target handled by the file copy).
     */
    protected function c(string $code, string $text): string
    {
        return "\e[{$code}m{$text}\e[0m";
    }

    /**
     * Write a line to stderr (colored) and the log file (plain).
     */
    protected function write(string $colored, string $plain): void
    {
        if (! config('vibe.log.enabled')) {
            return;
        }

        if (config('vibe.log.console')) {
            $stderr = fopen('php://stderr', 'w');
            fwrite($stderr, $colored.PHP_EOL);
            fclose($stderr);
        }

        if ($file = config('vibe.log.file')) {
            file_put_contents($file, '['.date('H:i:s')."] {$plain}".PHP_EOL, FILE_APPEND);
        }
    }
}
