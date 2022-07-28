<?php

namespace Codohq\Binary\Commands\Codo;

use Codohq\Binary\Commands;
use Codohq\Binary\Commands\Command;

class ShellCommand extends Command
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'shell {container} {--s|--shell=/bin/sh}';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'Open a shell for the specified Docker container.';

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

    $container = $this->argument('container');

    $shell = $this->option('shell');

    return $this->callWithArgv(Commands\External\DockerComposeCommand::class, [
      'exec',
      $container,
      $shell,
    ], false);
  }
}
