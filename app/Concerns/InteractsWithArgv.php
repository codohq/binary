<?php

namespace Codohq\Binary\Concerns;

use Symfony\Component\Process\Process;

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
   *
   */
  public function runningProcess(string $command, array $arguments = [], ?string $workdir = null): int
  {
    list ($data, $environment) = $this->prepareProcess($command, $arguments);

    $process = new Process($data, $workdir, $environment);

    $process->run(function ($type, $buffer) {
      if ($type === Process::ERR) {
        return $this->error($buffer);
      }

      $this->info($buffer);
    });

    return $process->getExitCode();
  }

  /**
   * Execute the given command and return its status code & output.
   *
   * @param  string  $command
   * @param  array  $arguments  []
   * @param  string|null  $workdir  null
   * @return array
   */
  public function process(string $command, array $arguments = [], ?string $workdir = null): array
  {
    list ($data, $environment, $full) = $this->prepareProcess($command, $arguments);

    $this->command(implode(' ', $full));

    $process = new Process($data, $workdir, $environment);

    $process->run();

    return [
      $process->getExitCode(),
      $process->getOutput() ?: $process->getErrorOutput(),
    ];
  }

  /**
   * Execute the given command and return its status code & output.
   *
   * @param  string  $command
   * @param  array  $arguments  []
   * @param  string|null  $workdir  null
   * @return array
   */
  public function silentProcess(string $command, array $arguments = [], ?string $workdir = null): array
  {
    list ($data, $environment) = $this->prepareProcess($command, $arguments);

    $process = new Process($data, $workdir, $environment);

    $process->run();

    return [
      $process->getExitCode(),
      $process->getOutput() ?: $process->getErrorOutput(),
    ];
  }

  /**
   *
   */
  protected function prepareProcess(string $command, array $arguments): array
  {
    $data = array_merge(explode(' ', $command), $arguments);

    $codo = app('codo');

    if (! empty($codo['file'])) {
      $directory = dirname($codo['file']);

      $environment = [
        'CODO_UID'        => trim(shell_exec('id -u')),
        'CODO_GID'        => trim(shell_exec('id -g')),
        'CODO_BASEPATH'   => realpath($directory),
        'CODO_DOCKER'     => $codo['config']->get('codo.components.docker'),
        'CODO_ENTRYPOINT' => $codo['config']->get('codo.components.entrypoint'),
        'CODO_FRAMEWORK'  => $codo['config']->get('codo.components.framework'),
        'CODO_THEME'      => $codo['config']->get('codo.components.theme'),
      ];

      $combined = array_map(
        fn ($v, $k) => strtoupper($k).'='.$v,
        array_values($environment),
        array_keys($environment)
      );
    }

    return [$data, $environment ?? null, array_merge($combined ?? [], $data)];
  }

  /**
   * Call an artisan command using the system argv arguments.
   *
   * @param  string  $command
   * @param  array  $arguments  []
   * @return mixed
   */
  public function callWithArgv(string $command, array $arguments = []): mixed
  {
    $arguments = array_merge($arguments, $this->getArgv());

    $this->setArgv($arguments);

    return $this->call($command, $arguments);
  }
}
