<?php

namespace Codohq\Binary\Binaries;

use Codohq\Binary\Process;
use Codohq\Binary\CliString;
use Illuminate\Support\Collection;
use Symfony\Component\Process\Process as SymfonyProcess;

class Docker
{
  /**
   * Holds the current Codo project.
   *
   * @var array
   */
  protected array $codo;

  /**
   * Instantiate a new Docker object.
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
    $output = trim(shell_exec('docker --version'));

    $pattern = '/(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?/';

    preg_match($pattern, $output, $matches);

    return $matches[0] ?? null;
  }

  /**
   * Execute a raw Docker command.
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
   * Prepare the Docker command.
   *
   * @param  string  $command
   * @return \Codohq\Binary\Process
   */
  protected function createProcess(string $command): Process
  {
    $root = $this->codo['config']->getWorkingDirectory();

    $process = Process::fromString(<<<COMMAND

      docker {$command}

    COMMAND);

    $process->setWorkdir($root);

    return $process;
  }
}
