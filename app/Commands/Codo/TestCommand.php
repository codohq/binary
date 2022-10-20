<?php

namespace Codohq\Binary\Commands\Codo;

use Codohq\Binary\Commands\Command;
use Codohq\Binary\Commands\Services;
use Codohq\Binary\Contracts\Eligible;

class TestCommand extends Command implements Eligible
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
    $binary = $this->option('bin');

    if (! file_exists($binary)) {
      return $this->error("Could not locate test binary at '${binary}'.");
    }

    return $this->call(Services\PhpCommand::class, [
      $binary,
      ...$this->leftovers(),
    ]);
  }
}
