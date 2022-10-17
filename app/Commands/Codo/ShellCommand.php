<?php

namespace Codohq\Binary\Commands\Codo;

use Codohq\Binary\Commands\Command;
use Codohq\Binary\Contracts\Eligible;

class ShellCommand extends Command implements Eligible
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'shell {container}';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'Open a shell for the specified Docker container.';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $container = $this->argument('container');

    $command = implode(' ', $this->leftovers()) ?: '/bin/bash';

    return $this->binary->dockerCompose($container, "{$command}");
  }
}
