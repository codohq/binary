<?php

namespace Codohq\Binary\Services;

use Codohq\Binary\Contracts\Executable;
use Codohq\Binary\Concerns\InteractsWithProcesses;

class Mkcert implements Executable
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
    return array_merge(['mkcert'], $arguments);
  }

  /**
   * Retrieve the external program's version.
   *
   * @return string|null
   */
  public static function version(): ?string
  {
    return null;
  }
}
