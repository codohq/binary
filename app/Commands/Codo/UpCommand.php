<?php

namespace Codohq\Binary\Commands\Codo;

use Codohq\Binary\Commands\Command;
use Codohq\Binary\Commands\Services;
use Codohq\Binary\Contracts\Eligible;

class UpCommand extends Command implements Eligible
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
    return $this->call(Services\DockerComposeCommand::class, [
      'up',
      '--remove-orphans',
      '--force-recreate',
    ]);
  }
}
