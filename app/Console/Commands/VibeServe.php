<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

#[Signature('vibe {--host=127.0.0.1} {--port=8000}')]
#[Description('Boot the VibePHP server with live request logging')]
class VibeServe extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $host = (string) $this->option('host');
        $port = (string) $this->option('port');

        $this->banner($host, $port);

        $php = (new PhpExecutableFinder)->find(false) ?: PHP_BINARY;

        $process = new Process(
            [$php, base_path('artisan'), 'serve', "--host={$host}", "--port={$port}"],
            base_path(),
            timeout: null,
        );

        // Hand the underlying dev server a real terminal when we have one so its
        // output — including the runtime's per-request log lines on stderr —
        // streams straight through, colors and all.
        if (Process::isTtySupported()) {
            $process->setTty(true);

            return $process->run();
        }

        return $process->run(function ($type, $buffer): void {
            $this->output->write($buffer);
        });
    }

    /**
     * Print the startup banner.
     */
    protected function banner(string $host, string $port): void
    {
        $docroot = str_replace(base_path().DIRECTORY_SEPARATOR, '', (string) config('vibe.docroot'));

        $this->newLine();
        $this->line('  <fg=magenta;options=bold>🌀 VibePHP</> <fg=gray>— PHP that runs on vibes, not code</>');
        $this->line("  <fg=gray>serving</> <fg=cyan>{$docroot}/</> <fg=gray>at</> <fg=cyan>http://{$host}:{$port}</>");
        $this->line('  <fg=gray>requests are logged below: timing · tools · tokens · cost</>');
        $this->newLine();
    }
}
