<?php

namespace Codohq\Binary;

use LaravelZero\Framework\Commands\Command;
use Codohq\Binary\Concerns\InteractsWithEligibility;
use Codohq\Binary\Exceptions\CodoProjectIsDownException;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Intermediary
{
  use InteractsWithEligibility;

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
    $status = ($this->isEligible() or (new Binaries\DockerCompose)->isRunning($container));

    $this->ineligible();

    return $status;
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
   * Run a Docker command.
   *
   * @param  string  $command
   * @param  string|null  $workdir  null
   * @return integer
   */
  public function docker(string $command, ?string $workdir = null): int
  {
    $process = (new Binaries\Docker($workdir))->raw($command);

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

  /**
   * Run a MariaDB query.
   *
   * @param  string  $query
   * @return integer
   */
  public function mariadb(string $query): int
  {
    if (! $this->isContainerRunning('mariadb')) {
      throw new CodoProjectIsDownException;
    }

    return $this->dockerCompose('mariadb', <<<COMMAND

      /bin/bash -c "/usr/bin/mysql -uroot -p\${MYSQL_ROOT_PASSWORD} -s -N -e \"{$query}\""

    COMMAND);
  }
}
