<?php

namespace Codohq\Binary\Commands\Services;

use Codohq\Binary\Binaries;
use Codohq\Binary\Commands\Command;
use Codohq\Binary\Contracts\Eligible;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MkcertCommand extends Command implements Eligible
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'mkcert';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'Mkcert wrapper command.';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $command = implode(' ', $this->leftovers());

    $process = (new Binaries\Mkcert)->raw("-install {$command}");

    $process->run();

    if (! $process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    return $process->getExitCode();
  }
}
