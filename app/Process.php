<?php

namespace Codohq\Binary;

use Closure;
use Codohq\Binary\Commands\Command;
use Symfony\Component\Process\Process as SymfonyProcess;

class Process
{
  /**
   * Holds the current Codo project.
   *
   * @var array
   */
  protected array $codo;

  /**
   * Holds the working directory.
   *
   * @var string|null
   */
  protected ?string $workdir = null;

  /**
   * Holds the environment variables.
   *
   * @var array
   */
  protected array $envVariables = [];

  /**
   * Instantiate a new process object.
   *
   * @param  array  $arguments
   * @return void
   */
  public function __construct(protected array $arguments)
  {
    $this->codo = resolve('codo');
  }

  /**
   * Instantiate a new process object from a string.
   *
   * @param  \Codohq\Binary\CliString|string  $command
   * @return $this
   */
  public static function fromString(CliString|string $command): self
  {
    $input = $command instanceof CliString ? $command : new CliString($command);

    return new static($input->toArray());
  }

  /**
   * Set the process working directory.
   *
   * @param  string  $workdir
   * @return void
   */
  public function setWorkdir(string $workdir): void
  {
    $this->workdir = $workdir;
  }

  /**
   * Retrieve the working directory.
   *
   * @return string
   */
  public function getWorkdir(): string
  {
    return $this->workdir ?? getcwd();
  }

  /**
   * Set the process working directory.
   *
   * @param  array  $variables
   * @return void
   */
  public function setEnvVariables(array $variables): void
  {
    $this->envVariables = $envVariables;
  }

  /**
   * Retrieve all of the environment variables.
   *
   * @return array
   */
  protected function getEnvVariables(): array
  {
    return array_merge(
      $this->codo['config']->environmentVariables(),
      $this->envVariables,
    );
  }

  /**
   * Print the full command.
   *
   * @return string
   */
  public function print(): string
  {
    $output = '';

    foreach ($this->getEnvVariables() as $variable => $value) {
      $output .= "{$variable}={$value} ";
    }

    $output .= implode(' ', $this->arguments);

    return trim($output);
  }

  /**
   * Build the process object.
   *
   * @return \Symfony\Component\Process\Process
   */
  public function build(): SymfonyProcess
  {
    $process = new SymfonyProcess(
      $this->arguments,
      $this->getWorkdir(),
      $this->getEnvVariables(),
    );

    $process->setTty(SymfonyProcess::isTtySupported());
    $process->setTimeout(null);
    $process->setIdleTimeout(null);

    if (! Command::showOutput()) {
      $process->disableOutput();
    }

    return $process;
  }
}
