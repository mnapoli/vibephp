<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Lets the Vibe PHP "runtime" read additional source files from the docroot,
 * the way a real interpreter would resolve `include`, `require`,
 * `file_get_contents`, etc. Reads are confined to the configured docroot.
 */
class ReadVibeFile implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Read the raw source of another file from the PHP project (the docroot), '
            .'relative to the docroot root. Use this whenever the script you are executing '
            .'includes, requires, or otherwise reads another file (e.g. include "header.php", '
            .'require_once "lib/db.php", file_get_contents("data.json")). '
            .'Returns the file contents, or an error string if the file does not exist.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $docroot = realpath((string) config('vibe.docroot'));

        if ($docroot === false) {
            return 'ERROR: the Vibe docroot does not exist.';
        }

        $relative = ltrim(str_replace('\\', '/', (string) $request['path']), '/');
        $target = realpath($docroot.DIRECTORY_SEPARATOR.$relative);

        // Confine reads to the docroot, defeating any `../` traversal.
        if ($target === false || ! str_starts_with($target, $docroot.DIRECTORY_SEPARATOR)) {
            return "ERROR: file not found in docroot: {$relative}";
        }

        if (! is_file($target)) {
            return "ERROR: not a readable file: {$relative}";
        }

        return "===== START OF {$relative} =====\n"
            .file_get_contents($target)
            ."\n===== END OF {$relative} =====";
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'path' => $schema->string()
                ->description('Path to the file relative to the docroot, e.g. "header.php" or "lib/db.php".')
                ->required(),
        ];
    }
}
