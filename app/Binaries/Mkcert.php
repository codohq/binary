<?php

namespace Codohq\Binary\Binaries;

use Codohq\Binary\Process;
use Codohq\Binary\CliString;
use Illuminate\Support\Collection;
use Symfony\Component\Process\Process as SymfonyProcess;

class Mkcert
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
   * @return void
   */
  public function __construct()
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
    return trim(shell_exec('mkcert --version'));
  }

  /**
   * Execute a raw Mkcert command.
   *
   * @param  string  $command
   * @return \Codohq\Binary\Process
   */
  public function raw(string $command): SymfonyProcess
  {
    $process = $this->createProcess($command);

    return $process->build();
  }

  /**
   * Prepare the Mkcert command.
   *
   * @param  string  $command
   * @return \Codohq\Binary\Process
   */
  protected function createProcess(string $command): Process
  {
    $root = $this->codo['config']->root()->asAbsolute();

    $process = Process::fromString(<<<COMMAND

      mkcert {$command}

    COMMAND);

    $process->setWorkdir($root);

    return $process;
  }
}
