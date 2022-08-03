<?php

namespace Codohq\Binary\Contracts;

use Symfony\Component\Process\Process;
use Codohq\Binary\Contracts\Commandable;

interface Executable
{
  /**
   * Prepare the external program.
   *
   * @param  array  $arguments
   * @return array
   */
  public function prepare(array $arguments): array;

  /**
   * Retrieve the external program's version.
   *
   * @return string|null
   */
  public static function version(): ?string;

  /**
   * Initiate a one-off command process.
   *
   * @param  \Codohq\Binary\Contracts\Commandable  $command
   * @return \Symfony\Component\Process\Process
   */
  public function once(Commandable $command): Process;

  /**
   * Initiate a continuous command process.
   *
   * @param  \Codohq\Binary\Contracts\Commandable  $command
   * @return \Symfony\Component\Process\Process
   */
  public function on(Commandable $command): Process;
}
