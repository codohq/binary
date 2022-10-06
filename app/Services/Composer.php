<?php

namespace Codohq\Binary\Services;

use Codohq\Binary\Contracts\Executable;
use Codohq\Binary\Concerns\InteractsWithProcesses;

class Composer implements Executable
{
  use InteractsWithProcesses;

  /**
   * Instantiate a new service object.
   *
   * @param  boolean  $local  false
   * @param  string|null  $workdir  null
   * @return void
   */
  public function __construct(protected bool $local = false, protected ?string $workdir = null)
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
      return array_merge(['composer'], $arguments);
    }

    if (is_dir($this->workdir)) {
      $workdir = [
        '-v', $this->workdir.PATH_SEPARATOR.'/tmp/volume',
        '-w', '/tmp/volume',
      ];
    }

    return (new DockerCompose)->prepare(array_merge([
      'run',
      '--user', implode(PATH_SEPARATOR, [getmyuid(), getmygid()]),
      '--interactive',
      '--tty',
      '--use-aliases',
      ...$workdir ?? [],
      'php',
      'composer',
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
