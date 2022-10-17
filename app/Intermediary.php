<?php

namespace Codohq\Binary;

use LaravelZero\Framework\Commands\Command;
use Codohq\Binary\Exceptions\CodoProjectIsDownException;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Intermediary
{
  /**
   * Holds the current Codo configuration.
   *
   * @var array
   */
  protected array $codo;

  /**
   * Instantiate a new command executor instance.
   *
   * @param  \LaravelZero\Framework\Commands\Command  $console
   * @return void
   */
  public function __construct(protected Command $console)
  {
    $this->codo = resolve('codo');
  }

  /**
   * Check if the given container is running or not.
   *
   * @param  string  $container
   * @return boolean
   */
  public function isContainerRunning(string $container): bool
  {
    return (new Binaries\DockerCompose)->isRunning($container);
  }

  /**
   * Run a Docker Compose command.
   *
   * @param  string  $container
   * @param  string  $command
   * @param  string|null  $workdir  null
   * @return integer
   */
  public function dockerCompose(string $container, string $command, ?string $workdir = null): int
  {
    $process = (new Binaries\DockerCompose($workdir))->execOrRun($container, $command);

    $process->run();

    if (! $process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    return $process->getExitCode();
  }

  /**
   * Run a Composer command.
   *
   * @param  string  $command
   * @return integer
   */
  public function composer(string $command): int
  {
    if (! $this->isContainerRunning('php')) {
      throw new CodoProjectIsDownException;
    }

    return $this->dockerCompose('php', "composer {$command}");
  }

  /**
   * Run a Laravel artisan command.
   *
   * @param  string  $command
   * @return integer
   */
  public function artisan(string $command): int
  {
    if (! $this->isContainerRunning('php')) {
      throw new CodoProjectIsDownException;
    }

    return $this->dockerCompose('php', "php ./artisan {$command}");
  }

  /**
   * Run a Caddy command.
   *
   * @param  string  $command
   * @return integer
   */
  public function caddy(string $command): int
  {
    if (! $this->isContainerRunning('caddy')) {
      throw new CodoProjectIsDownException;
    }

    return $this->dockerCompose('caddy', "caddy {$command}");
  }

  /**
   * Run a NPM command.
   *
   * @param  string  $command
   * @param  string|null  $workdir  null
   * @return integer
   */
  public function npm(string $command, ?string $workdir = null): int
  {
    if (! $this->isContainerRunning('node')) {
      throw new CodoProjectIsDownException;
    }

    return $this->dockerCompose('node', "npm {$command}", $workdir);
  }
}
