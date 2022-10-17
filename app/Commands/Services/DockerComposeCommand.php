<?php

namespace Codohq\Binary\Commands\Services;

use Codohq\Binary\Binaries;
use Codohq\Binary\Commands\Command;
use Codohq\Binary\Contracts\Eligible;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DockerComposeCommand extends Command implements Eligible
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'docker:compose {--w|--workdir=}';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'Docker Compose wrapper command.';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $workdir = $this->option('workdir');

    $command = implode(' ', $this->leftovers());

    $process = (new Binaries\DockerCompose($workdir))->raw($command);

    $process->run();

    if (! $process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    return $process->getExitCode();
  }
}
