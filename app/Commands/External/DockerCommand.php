<?php

namespace Codohq\Binary\Commands\External;

use Illuminate\Support\Arr;
use Codohq\Binary\Configuration;
use function Termwind\{ render };
use Codohq\Binary\Services\Docker;
use Codohq\Binary\Commands\Command;
use Codohq\Binary\Contracts\Commandable;

class DockerCommand extends Command
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'docker';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'Docker wrapper command.';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();

    $this->ignoreValidationErrors();
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    if ($this->isIneligible()) {
      return $this->ineligible();
    }

    $codo = app('codo');

    $process = (new Docker)->on(
      $command = $this->buildCommand($codo['config'])
    );

    $process->run();

    return $process->getExitCode();
  }

  /**
   * Build the command.
   *
   * @param  \Codohq\Binary\Configuration  $codo
   * @return \Codohq\Binary\Contracts\Commandable
   */
  protected function buildCommand(Configuration $codo): Commandable
  {
    return new class($this, $codo) implements Commandable
    {
      /**
       * Instantiate a new anonymous commandable object.
       *
       * @param  \Codohq\Binary\Commands\Command  $console
       * @param  \Codohq\Binary\Configuration  $codo
       * @return void
       */
      public function __construct(protected Command $console, protected Configuration $codo)
      {
        //
      }

      /**
       * Get the instance as an array.
       *
       * @return array<TKey, TValue>
       */
      public function toArray()
      {
        return $this->console->getArgv();
      }

      /**
       * Retrieve the working directory for the command.
       *
       * @return string|null
       */
      public function workspace(): ?string
      {
        return null;
      }

      /**
       * Retrieve the environment variables for which the command is run with.
       *
       * @return array
       */
      public function environment(): array
      {
        return $this->codo->getEnvironmentVariables();
      }
    };
  }
}
