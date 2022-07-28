<?php

namespace Codohq\Binary\Services;

use Illuminate\Support\Arr;
use Codohq\Binary\Contracts\ExternalProgram;
use Codohq\Binary\Concerns\InteractsWithProcesses;

class DockerCompose implements ExternalProgram
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
    $codo = app('codo');

    $files = Arr::collapse(array_map(fn ($x) => ['-f', $x], array_filter([
      $codo['config']->getDocker('docker-compose.yml', false),
      $codo['config']->getDocker(sprintf('docker-compose.%s.yml', $codo['config']->getEnvironment()), false),
    ])));

    $arguments = [
      '--project-name',   $codo['config']->getProject(),
      '--env-file',       $codo['config']->getEntrypoint('.env', true),
      ...$files,
      ...$arguments,
    ];

    return array_merge(['docker', 'compose'], $arguments);
  }
}
