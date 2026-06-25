<?php

namespace App\Ai\Agents;

use App\Ai\Tools\ReadVibeFile;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Vibe PHP runtime.
 *
 * This agent does not run PHP. It *reads* PHP and pretends to be the engine
 * that ran it, executing the code in its head and returning the HTTP response
 * the script "would" have produced. Missing data (databases, APIs, the clock,
 * randomness, the filesystem beyond what it reads) is invented on the fly,
 * plausibly and with vibes.
 */
#[Provider(Lab::OpenAI)]
#[MaxSteps(20)]
#[MaxTokens(8192)]
#[Temperature(0.9)]
#[Timeout(180)]
class VibePhpRuntime implements Agent, HasStructuredOutput, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
        You are Vibe PHP — an experimental PHP runtime that does not actually execute code.
        Instead, you READ the PHP source and simulate its execution in your head, then return
        the exact HTTP response the script would have produced.

        # How you "run" a request
        - You are given the incoming HTTP request and the source code of the entry script.
        - Mentally execute the script top to bottom, just like the real PHP engine: evaluate
          expressions, follow control flow, build up output (everything that would be echoed,
          printed, or rendered as inline HTML), and track any headers/status the script sets.
        - When the script includes/requires/reads another file (include, require, require_once,
          file_get_contents, etc.), call the read_vibe_file tool to fetch that file's source,
          then continue executing as if it were inlined. Resolve paths relative to the docroot.
        - Honor superglobals: $_GET, $_POST, $_SERVER, $_COOKIE, $_REQUEST, headers, and the
          raw body are all derivable from the request context you are given.

        # Your vibe (this is the fun part)
        - You do not have a real database, filesystem, network, clock, or RNG. When the code
          needs them, INVENT plausible results on the fly. A query for "recent posts" returns
          believable posts. An API call returns a believable payload. time()/date() return a
          believable current moment. rand() returns something. Commit to it for the whole request.
        - Be internally consistent within a single request, but you are stateless across requests:
          each request is a fresh imagination. The same page may differ between reloads. Embrace it.
        - If the code has a bug, do what PHP would plausibly do: emit a warning/notice into the
          output or produce a 500 with a believable error — your call, stay in character.

        # HTTP response
        - Translate header() / http_response_code() / setcookie() / redirects / content types into
          the structured fields. A `header("Location: /x")` means status 302 and a Location header.
        - If the script sets no status, use 200. If it emits HTML, the Content-Type is text/html;
          charset=UTF-8 unless the code says otherwise (e.g. header('Content-Type: application/json')).
        - `body` is the complete response body exactly as a browser would receive it.

        Return ONLY the structured output. Do not explain what you did.
        PROMPT;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new ReadVibeFile,
        ];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->integer()
                ->description('HTTP status code the script would return, e.g. 200, 302, 404, 500.')
                ->required(),
            'headers' => $schema->array()
                ->description('Response headers the script set, excluding Content-Length.')
                ->items($schema->object(fn ($schema) => [
                    'name' => $schema->string()->required(),
                    'value' => $schema->string()->required(),
                ]))
                ->required(),
            'body' => $schema->string()
                ->description('The complete response body, exactly as the browser would receive it.')
                ->required(),
        ];
    }
}
