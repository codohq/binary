<?php

namespace Codohq\Binary\Concerns;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Output\BufferedOutput;

trait InteractsWithArgv
{
  /**
   * Retrieve the system argv arguments.
   *
   * @return array
   */
  public function getArgv(): array
  {
    return array_slice($_SERVER['argv'], 2);
  }

  /**
   * Set the system argv arguments.
   *
   * @param  array  $arguments
   * @return void
   */
  public function setArgv(array $arguments): void
  {
    $_SERVER['argv'] = array_merge(array_slice($_SERVER['argv'], 0, 2), $arguments);
  }

  /**
   * Call an artisan command using the system argv arguments.
   *
   * @param  string  $command
   * @param  array  $arguments  []
   * @param  boolean  $merge  true
   * @return mixed
   */
  public function callWithArgv(string $command, array $arguments = [], bool $merge = true): mixed
  {
    if ($merge) {
      $arguments = array_merge($arguments, $this->getArgv());
    }

    $this->setArgv($arguments);

    return $this->call($command, $arguments);
  }
}
