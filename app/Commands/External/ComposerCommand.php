<?php

namespace Codohq\Binary\Commands\External;

use Codohq\Binary\Configuration;
use function Termwind\{ render };
use Codohq\Binary\Commands\Command;
use Codohq\Binary\Services\Composer;
use Codohq\Binary\Contracts\Commandable;

class ComposerCommand extends Command
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'composer {--l|--local} {--w|workdir=} {--c|--container=php}';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'Composer wrapper command.';

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

    $command = $this->buildCommand($codo['config']);

    $process = (new Composer(
      local: $this->option('local'),
      workdir: $command->workspace(),
    ))->on($command);

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
       * Holds the composer.json path.
       *
       * @var string|null
       */
      protected ?string $json;

      /**
       * Instantiate a new anonymous commandable object.
       *
       * @param  \Codohq\Binary\Commands\Command  $console
       * @param  \Codohq\Binary\Configuration  $codo
       * @return void
       */
      public function __construct(protected Command $console, protected Configuration $codo)
      {
        $this->json = $console->locateFile(
          sprintf('%s/composer.json', getcwd())
        );
      }

      /**
       * Get the instance as an array.
       *
       * @return array<TKey, TValue>
       */
      public function toArray()
      {
        return $this->console->getExternalArguments();
      }

      /**
       * Retrieve the working directory for the command.
       *
       * @return string|null
       */
      public function workspace(): ?string
      {
        return $this->console->option('workdir') ?? (
          $this->json ? dirname($this->json) : getcwd()
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
