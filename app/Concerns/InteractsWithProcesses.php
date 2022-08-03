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

    $process = new Process($cmd, $command->workspace(), $command->environment());

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

    $process = new Process($cmd, $command->workspace(), $command->environment());

    $process->setTty(Process::isTtySupported());
    $process->setTimeout(null);
    $process->setIdleTimeout(null);

    return $process;
  }
}
