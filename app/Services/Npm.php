<?php

namespace Codohq\Binary\Services;

use Codohq\Binary\Contracts\Executable;
use Codohq\Binary\Concerns\InteractsWithProcesses;

class Npm implements Executable
{
  use InteractsWithProcesses;

  /**
   * Instantiate a new service object.
   *
   * @param  boolean  $local  false
   * @return void
   */
  public function __construct(protected bool $local = false)
  {
    //
  }

  /**
   * Prepare the external program.
   *
   * @param  array  $arguments
   * @return array
   */
  public function prepare(array $arguments): array
  {
    if ($this->local) {
      return array_merge(['npm'], $arguments);
    }

    return (new DockerCompose)->prepare(array_merge([
      'exec',
      'node',
      'npm',
    ], $arguments));
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
