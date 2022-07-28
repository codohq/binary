<?php

namespace Codohq\Binary\Services;

use Codohq\Binary\Contracts\ExternalProgram;
use Codohq\Binary\Concerns\InteractsWithProcesses;

class Artisan implements ExternalProgram
{
  use InteractsWithProcesses;

  /**
   * Prepare the external program.
   *
   * @param  array  $arguments
   * @return array
   */
  public function prepare(array $arguments): array
  {
    return array_merge(['php', './artisan'], $arguments);
  }
}