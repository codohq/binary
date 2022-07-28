<?php

namespace Codohq\Binary\Commands\External;

use Codohq\Binary\Services\Npm;
use Codohq\Binary\Configuration;
use function Termwind\{ render };
use Codohq\Binary\Commands\Command;
use Codohq\Binary\Contracts\Commandable;

class NpmCommand extends Command
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'npm {--w|workdir=}';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'NPM wrapper command.';

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

    $process = (new Npm)->on(
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
       * Holds the package.json path.
       *
       * @var string|null
       */
      protected ?string $package;

      /**
       * Instantiate a new anonymous commandable object.
       *
       * @param  \Codohq\Binary\Commands\Command  $console
       * @param  \Codohq\Binary\Configuration  $codo
       * @return void
       */
      public function __construct(protected Command $console, protected Configuration $codo)
      {
        $this->package = $console->locateFile(
          sprintf('%s/package.json', getcwd())
        );
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
        return $this->console->option('workdir') ?? (
          $this->package ? dirname($this->package) : $this->codo->getTheme(null, true)
        );
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
