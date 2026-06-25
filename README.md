<h1 align="center">🌀 VibePHP</h1>

<p align="center"><em>PHP that runs on vibes, not code.</em></p>

<p align="center">
<code>No opcodes.</code> · <code>No Zend Engine.</code> · <code>Just vibes.</code>
</p>

---

## What is this

VibePHP is a new generation PHP runtime and web server that runs PHP faster* and better*.

There is no interpreter. There is no compiler. When a request comes in, your PHP source is handed to an AI that reads it, runs it **in its head**, makes up whatever it needs to (the database, the clock, the network, the truth), and hands back the HTTP response it reckons the code *would* have produced.

It is not deterministic. It is not cheap. It is not correct. It is, however, very vibe.

```php
<?php
$posts = $db->query('SELECT * FROM posts ORDER BY published_at DESC');
// there is no $db. there is no database. there are no posts.
// the AI will invent five believable ones anyway. ✨
```

_\* debatable_

## Why

Because PHP-FPM is too stable and FrankenPHP too fast.

## How it works

```
   GET /posts/42
        │
        ▼
┌───────────────────┐     reads the file, doesn't run it
│  VibeController   │ ───────────────────────────────────────┐
│  routes URL→file  │                                        │
└───────────────────┘                                        ▼
                                              ┌──────────────────────────────┐
   HTTP response  ◄──── {status,headers,body} │      VibePhpRuntime (AI)     │
        ▲                                     │  "i am the php engine now"   │
        │                                     │  • executes in its head      │
        └──────────────────────────────────── │  • invents missing data      │
                                              │  • reads includes via a tool │
                                              └──────────────────────────────┘
```

1. **A request arrives.** The catch-all route maps the URL to a file in `vibe/` (`/about` → `about.php`), falling back to `index.php` as a front controller.
2. **The source is read, not run.** Code + the full HTTP request context get handed to the `VibePhpRuntime` agent.
3. **The AI "executes" it.** It follows the control flow, fetches `include`/`require`d files on demand via the `ReadVibeFile` tool, and **improvises every missing piece** — DB rows, `date()`, `rand()`, API payloads — plausibly and with full commitment.
4. **It returns `{ status, headers, body }`.** Laravel sends that back as a real HTTP response. `header('Location: …')` becomes a 302. `header('Content-Type: application/json')` is honored. The whole charade holds up.

It's stateless across requests, so reloading the same page gives you different data every time. That's not a bug. That's the vibe.

## Quickstart

```bash
# 1. Point it at an OpenAI key (the engine needs a brain)
export OPENAI_API_KEY=sk-...

# 2. Boot the "interpreter"
php artisan serve

# 3. Visit the site that builds itself as you look at it
open http://localhost:8000
```

Then wander around:

| Route | What "happens" |
|-|-|
| `/` | Home page listing 5 freshly-hallucinated blog posts |
| `/about` | An about page (uptime: a number the AI feels good about) |
| `/posts/42` | A full article that has never existed until you asked |
| `/api/status` | JSON endpoint with imagined load metrics |

Refresh any of them. It's never the same site twice.

## Write your own page

Drop a `.php` file in `vibe/` and it's instantly "served." Write whatever PHP you want — the more it relies on a database, filesystem, or external API that **isn't there**, the better the vibe.

```php
<?php // vibe/weather.php
header('Content-Type: application/json');
$city = $_GET['city'] ?? 'Paris';
$temp = fetchTemperatureFromSomeApiThatDoesNotExist($city);
echo json_encode(['city' => $city, 'temp_c' => $temp]);
```

`fetchTemperatureFromSomeApiThatDoesNotExist()` is not defined anywhere. It will return a believable temperature regardless. You're welcome.

## Configuration

`config/vibe.php`:

| Key | Env | Default | Meaning |
|-|-|-|-|
| `docroot` | `VIBE_DOCROOT` | `vibe/` | Where your "executable" PHP lives |
| `model` | `VIBE_MODEL` | provider default | Which brain interprets your code |

The provider (OpenAI) is set on the `App\Ai\Agents\VibePhpRuntime` agent via `laravel/ai`.

## Caveats (the vibe has a cost)

- **Every request is an LLM call.** It's slow and it costs money. This is the price of vibes.
- **There is no correctness guarantee.** There is, in fact, an anti-guarantee.
- **Do not put this in production.** Or do. We're not your boss. But don't.
- **State does not persist.** Each request is a fresh hallucination from a blank mind.

## Tests

The plumbing is tested for real (the AI is faked, the engine is not):

```bash
php artisan test --filter=VibeEngineTest
```

## ☁️ Vibe Cloud — coming soon

Running VibePHP on your own machine is fine, but it's not web scale. Vibe Cloud is fully managed, serverless VibePHP hosting. Deploy with `vibe up` and focus on running your business.

### Cloud Particles™

Vibe Cloud doesn't bill you for servers, containers, or even functions. It bills you for **particles**.

- **1 particle = 1 MB of memory.** Your app's footprint is measured to the megabyte, in real time.
- **Particles autoscale per MB.** Allocate another array, spawn another particle. Free a string, the particle decays. You pay for exactly the memory you're using, sampled continuously, billed per particle-millisecond.
- **Memory is quantized.** You cannot allocate half a megabyte. There is no `0.5` particle.
- **Heisenberg Tier (Enterprise).** On our top tier, you can know either how much memory your app is using *or* how much it costs. Never both at once.

### Idle Cycle Reclamation

While the model is "executing" your PHP, your CPU is sitting idle for whole seconds, politely waiting on an inference API. That's billions of wasted cycles, per request, per particle.

Vibe Cloud puts those idle cycles to work with **Speculative Pre-Vibing**.

While your request is out at the inference API, every spare core races ahead to *pre-hallucinate the response to the NEXT request that might be received*. By the time the user clicks to another page, the page is already rendered. Zero perceived latency, and the cycles were *reclaimed*, which goes on the sustainability report. Vibe Cloud is proud to be **carbon-neutral-adjacent**.

## Built with

[Laravel 13](https://laravel.com) · [`laravel/ai`](https://github.com/laravel/ai) · a profound disregard for how interpreters are supposed to work.

---

<p align="center"><sub>PHP, but make it manifest. ✨</sub></p>
