<?php

namespace Codohq\Binary\Commands\Codo;

use Codohq\Binary\Commands;
use Codohq\Binary\Commands\Command;

class DownCommand extends Command
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'down';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'Shutdown the Codo project.';

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
      'down',
      '--remove-orphans',
    ]);
  }
}
