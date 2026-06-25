<?php

use App\Ai\Agents\VibePhpRuntime;
use Laravel\Ai\Prompts\AgentPrompt;

it('serves a script by handing its source to the runtime and mapping the response', function () {
    VibePhpRuntime::fake(fn (string $prompt) => [
        'status' => 200,
        'headers' => [
            ['name' => 'X-Powered-By', 'value' => 'Vibe PHP'],
        ],
        'body' => '<h1>Welcome to Vibe PHP</h1>',
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Welcome to Vibe PHP', false);
    $response->assertHeader('X-Powered-By', 'Vibe PHP');
    $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');

    // The entry script's source must actually reach the runtime.
    VibePhpRuntime::assertPrompted(fn (AgentPrompt $prompt) => $prompt->contains('Entry script: index.php')
        && $prompt->contains('new PDO('));
});

it('lets the runtime control status code and content type', function () {
    VibePhpRuntime::fake(fn (string $prompt) => [
        'status' => 200,
        'headers' => [
            ['name' => 'Content-Type', 'value' => 'application/json'],
        ],
        'body' => '{"status":"ok"}',
    ]);

    $response = $this->get('/api/status');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/json');
    expect($response->getContent())->toBe('{"status":"ok"}');
});

it('honors imagined redirects', function () {
    VibePhpRuntime::fake(fn (string $prompt) => [
        'status' => 302,
        'headers' => [
            ['name' => 'Location', 'value' => '/about'],
        ],
        'body' => '',
    ]);

    $this->get('/posts/42')
        ->assertStatus(302)
        ->assertHeader('Location', '/about');
});

it('returns a real 404 when no script and no index.php can handle the URL', function () {
    config()->set('vibe.docroot', base_path('tests/fixtures/empty-docroot'));

    $this->get('/whatever')->assertNotFound();

    VibePhpRuntime::assertNeverPrompted();
});
