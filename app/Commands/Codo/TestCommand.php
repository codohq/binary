<?php

namespace Codohq\Binary\Commands\Codo;

use Codohq\Binary\Commands;
use Codohq\Binary\Commands\Command;

class TestCommand extends Command
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'test {--bin=vendor/bin/pest}';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'Run your applications tests.';

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

    $binary = $this->option('bin');

    if (! file_exists($binary)) {
      return $this->error("Could not locate test binary at '${binary}'.");
    }

    return $this->call(Commands\External\PhpCommand::class, [
      $binary,
    ]);
  }
}
