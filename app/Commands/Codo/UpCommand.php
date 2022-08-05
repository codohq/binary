<?php

namespace Codohq\Binary\Commands\Codo;

use Codohq\Binary\Commands;
use Codohq\Binary\Commands\Command;

class UpCommand extends Command
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
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    if ($this->isIneligible()) {
      return $this->ineligible();
    }

    return $this->call(Commands\External\DockerComposeCommand::class, [
      'up',
      '--force-recreate',
      '--remove-orphans',
      '--build',
    ]);
  }
}
