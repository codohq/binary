<?php

namespace Codohq\Binary\Commands\Services;

use Codohq\Binary\Commands\Command;
use Codohq\Binary\Contracts\Eligible;

class NpxCommand extends Command implements Eligible
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'npx {--c|--container=node} {--w|--workdir=}';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'NPX wrapper command.';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $container = $this->option('container');

    $workdir = $this->option('workdir');

    $command = implode(' ', $this->leftovers());

    return $this->binary->dockerCompose($container, "npx {$command}", $workdir);
  }
}
