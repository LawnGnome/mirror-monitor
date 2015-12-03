<?php

namespace LawnGnome\MirrorMonitor;

use GuzzleHttp\{Client, Pool, Promise};
use GuzzleHttp\Psr7\{Request, Response};

class MonitorCycle {
  protected $needle;
  protected $timeout;
  protected $urls;

  public function __construct(array $urls, int $timeout, string $needle) {
    $this->needle = $needle;
    $this->timeout = $timeout;
    $this->urls = $urls;
  }

  public function run(): array {
    $success = [];
    $failed = [];

    $client = new Client(['timeout' => $this->timeout]);
    $pool = new Pool($client, $this->requests(), [
      'concurrency' => count($this->urls),
      'fulfilled'   => function (Response $response, string $url) use (&$success, &$failed) {
        if (false !== strpos((string) $response->getBody(), $this->needle)) {
          $success[] = $url;
        } else {
          $failed[] = $url;
        }
      },
    ]);
    $pool->promise()->wait();

    $failed = array_merge($failed, array_diff($this->urls, $success, $failed));
    return [$success, $failed];
  }

  protected function requests() {
    foreach ($this->urls as $url) {
      yield $url => new Request('GET', $url);
    }
  }
}
