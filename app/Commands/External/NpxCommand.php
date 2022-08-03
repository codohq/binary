<?php

namespace Codohq\Binary\Commands\External;

use Codohq\Binary\Services\Npx;

class NpxCommand extends NpmCommand
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'npx {--w|workdir=}';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'Npx wrapper command.';

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

    $process = (new Npx)->on(
      $command = $this->buildCommand($codo['config'])
    );

    $process->run();

    return $process->getExitCode();
  }
}
