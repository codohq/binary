<?php

namespace Codohq\Binary\Binaries;

use Codohq\Binary\Process;
use Codohq\Binary\CliString;
use Illuminate\Support\Collection;
use Symfony\Component\Process\Process as SymfonyProcess;

class DockerCompose
{
  /**
   * Holds the current Codo project.
   *
   * @var array
   */
  protected array $codo;

  /**
   * Instantiate a new Docker Compose object.
   * 
   * @param  string|null  $workdir  null
   * @return void
   */
  public function __construct(protected ?string $workdir = null)
  {
    $this->codo = resolve('codo');
  }

  /**
   * Retrieve the version of the binary.
   *
   * @return string|null
   */
  public static function version(): ?string
  {
    $output = trim(shell_exec('docker compose version'));

    $pattern = '/(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?/';

    preg_match($pattern, $output, $matches);

    return $matches[0] ?? null;
  }

  /**
   * Retrieve the compose file paths.
   *
   * @return \Illuminate\Support\Collection
   */
  protected function composeFiles(): Collection
  {
    $files = ['docker-compose.yml', 'docker-compose.{env}.yml'];

    return (new Collection($files))
      ->map(function ($file) {
        $file = str_ireplace('{env}', $this->codo['config']->environment(), $file);

        $path = $this->codo['config']->dockerPath()->asAbsolute($file);

        return $path;
      })
      ->filter()
      ->map(fn ($x) => ['-f', $x])
      ->collapse();
  }

  /**
   * Execute a Docker Compose `run` command.
   *
   * @param  string  $container
   * @param  string  $command
   * @return \Codohq\Binary\Process
   */
  public function run(string $container, string $command): SymfonyProcess
  {
    $workdir = $this->workdir ? implode(' ', [
      '--volume',
      "{$this->workdir}:/entrypoint",
      '--workdir',
      '/entrypoint',
    ]) : '';

    $process = $this->createProcess(<<<COMMAND

      run --interactive --tty --rm --no-deps
        {$workdir}
        {$container}
        {$command}

    COMMAND);

    return $process->build();
  }

  /**
   * Execute a Docker Compose `exec` command.
   *
   * @param  string  $container
   * @param  string  $command
   * @return \Codohq\Binary\Process
   */
  public function exec(string $container, string $command): SymfonyProcess
  {
    $workdir = $this->workdir ? "--workdir {$this->workdir}" : '';

    $process = $this->createProcess(<<<COMMAND

      exec --interactive --tty
        {$workdir}
        {$container}
        {$command}

    COMMAND);

    return $process->build();
  }

  /**
   * Execute a Docker Compose `exec` or `run` command depending on the container status.
   *
   * @param  string  $container
   * @param  string  $command
   * @return \Codohq\Binary\Process
   */
  public function execOrRun(string $container, string $command): SymfonyProcess
  {
    if ($this->isRunning($container)) {
      return $this->exec($container, $command);
    }

    return $this->run($container, $command);
  }

  /**
   * Check the status of the specified container.
   *
   * @param  string  $container
   * @return boolean
   */
  public function isRunning(string $container): bool
  {
    $process = $this->createProcess(<<<COMMAND

      ps -q {$container}

    COMMAND)->build();

    $process->disableOutput();

    $process->run();

    return $process->getExitCodeText() === 'OK';
  }

  /**
   * Execute a raw Docker Compose command.
   *
   * @param  string  $command
   * @return \Codohq\Binary\Process
   */
  public function raw(string $command): SymfonyProcess
  {
    $workdir = $this->workdir ? "--workdir {$this->workdir}" : '';

    $process = $this->createProcess($command);

    return $process->build();
  }

  /**
   * Prepare the Docker Compose command.
   *
   * @param  string  $command
   * @return \Codohq\Binary\Process
   */
  protected function createProcess(string $command): Process
  {
    $compose = $this->composeFiles()->join(' ');

    $project = $this->codo['config']->name();

    $root = $this->codo['config']->root()->asAbsolute();

    $envFile = $this->codo['config']->entrypoint()->asAbsolute('.env');

    $process = Process::fromString(<<<COMMAND

      docker compose
        {$compose}
        --project-name {$project}
        --project-directory {$root}
        --env-file {$envFile}
        {$command}

    COMMAND);

    $process->setWorkdir($root);

    return $process;
  }
}
