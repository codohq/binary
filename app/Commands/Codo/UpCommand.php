<?php

namespace Codohq\Binary\Commands\Codo;

use Codohq\Binary\Commands;
use Codohq\Binary\Commands\CodoCommand;

class UpCommand extends CodoCommand
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'up';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'Boot the Codo project.';

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

    return $this->callWithArgv(Commands\External\DockerComposeCommand::class, [
      'up',
      '--force-recreate',
      '--remove-orphans',
      '--build',
      '--detach',
      '--quiet-pull',
      '--no-color',
    ]);
  }
}
