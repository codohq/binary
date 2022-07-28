<?php

namespace Codohq\Binary\Services;

use Codohq\Binary\Contracts\ExternalProgram;
use Codohq\Binary\Concerns\InteractsWithProcesses;

class Docker implements ExternalProgram
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
    return array_merge(['docker'], $arguments);
  }

  /**
   * Retrieve the external program's version.
   *
   * @return string|null
   */
  public static function version(): ?string
  {
    $output = trim(shell_exec('docker --version'));

    $pattern = '/(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?/';

    preg_match($pattern, $output, $matches);

    return $matches[0] ?? null;
  }
}
