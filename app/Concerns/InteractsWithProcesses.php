<?php

namespace Codohq\Binary\Concerns;

use Symfony\Component\Process\Process;
use Codohq\Binary\Contracts\Commandable;

trait InteractsWithProcesses
{
  /**
   * Initiate a one-off command process.
   *
   * @param  \Codohq\Binary\Contracts\Commandable  $command
   * @return \Symfony\Component\Process\Process
   */
  public function once(Commandable $command): Process
  {
    $cmd = $this->prepare($command->toArray());

    return $this->onceRaw($cmd, $command->workspace(), $command->environment());
  }

  /**
   * Initiate a one-off command process.
   *
   * @param  array  $arguments
   * @param  string  $workdir
   * @param  array  $env  []
   * @return \Symfony\Component\Process\Process
   */
  public function onceRaw(array $arguments, string $workdir, array $env = []): Process
  {
    $process = new Process($arguments, $workdir, $env);

    return $process;
  }

  /**
   * Initiate a continuous command process.
   *
   * @param  \Codohq\Binary\Contracts\Commandable  $command
   * @return \Symfony\Component\Process\Process
   */
  public function on(Commandable $command): Process
  {
    $cmd = $this->prepare($command->toArray());

    return $this->onRaw($cmd, $command->workspace(), $command->environment());
  }

  /**
   * Initiate a continuous command process.
   *
   * @param  array  $arguments
   * @param  string  $workdir
   * @param  array  $env  []
   * @return \Symfony\Component\Process\Process
   */
  public function onRaw(array $arguments, string $workdir, array $env = []): Process
  {
    $process = new Process($arguments, $workdir, $env);

    $process->setTty(Process::isTtySupported());
    $process->setTimeout(null);
    $process->setIdleTimeout(null);

    return $process;
  }
}
