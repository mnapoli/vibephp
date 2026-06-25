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

An engine built for 2026 and beyond. PHP-FPM is stable, FrankenPHP is fast, VibePHP brings all the rest:

- Hype
- Generics
- Async/await
- Inline Go or Rust

VibePHP has no parser. Your code is never compiled, only *understood*. The contract is void, and so is every constraint that ever held the language back. Generics work because nothing stops them. So does any syntax you can dream up.

```php
class Prices<K: Stringable, V>
{
    private Map<K, V> $store = {};

    public async function fetchAll(K ...$symbols): Map<K, V>
    {
        // Inline Go mixed with PHP, who knows what this does except the AI
        return go {
            ch := make(chan V)
            for _, sym := range $symbols { go func() { ch <- await fetchPrice(sym) }() }
            for _, sym := range $symbols { $this->store[sym] = <-ch }
            return $this->store
        };
    }
}
```

## Benchmark

We benchmarked VibePHP against industry-leading runtimes. The numbers speak for themselves.

| Runtime         | Latency (p50) | Cost / request |
|-----------------|---------------|----------------|
| nginx + PHP-FPM | ~1 ms         | ~$0.00000x     |
| FrankenPHP      | ~1 ms         | ~$0.00000x     |
| **VibePHP**     | **~7 s**      | ~**$0.0063**   |

The [Labor Illusion study](https://www.hbs.edu/faculty/Pages/item.aspx?num=40158) (Buell & Norton, Harvard Business School) found that people value a service up to **8% more** when they can see it working for them, even *preferring* the slower version that returned identical results. VibePHP increases the perceived value of your PHP website by 700,000% which offsets the increased cost per request. It is a win-win.

## Quickstart

- Set an `OPENAI_API_KEY` in `.env`
- Create an `index.php` file in the `vibe/` directory:
  ```php
  <?php
  echo "Hello, world! The time is " . date('H:i:s');
  ```
- Run `php artisan vibe`
- Visit your PHP website at http://localhost:8000

## How it works

VibePHP itself is implemented using PHP, Laravel, [`laravel/ai`](https://laravel.com/ai), and GPT/Claude.

```
   GET /posts/42
        │
        ▼
┌───────────────────┐     reads the file, doesn't run it
│     Laravel       │ ───────────────────────────────────────┐
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

The core of the engine is implemented as a [Laravel AI agent](https://laravel.com/docs/13.x/ai-sdk#agents) in [`App\Ai\Agents\VibePhpRuntime`](app/Ai/Agents/VibePhpRuntime.php). It uses a [custom tool](app/Ai/Tools/ReadVibeFile.php) to read files from the `vibe/` directory on demand, so that includes and requires work as expected.

## Demo

- Clone this repo
- Set an `OPENAI_API_KEY` in `.env`
- Install the project with `composer run setup`
- Start the server with `php artisan vibe`
- Visit http://localhost:8000

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
