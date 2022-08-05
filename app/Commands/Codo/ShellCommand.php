<?php

namespace Codohq\Binary\Commands\Codo;

use Codohq\Binary\Commands;
use Codohq\Binary\Commands\Command;

class ShellCommand extends Command
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'shell {container} {--s|--shell=/bin/sh}';

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
    if ($this->isIneligible()) {
      return $this->ineligible();
    }

    $container = $this->argument('container');

    $shell = $this->option('shell');

    // $exitCode = $this->call(Commands\External\DockerComposeCommand::class, [
    //   'ps',
    //   $container,
    //   '--status', 'running',
    //   '-q',
    // ]);

    return $this->call(Commands\External\DockerComposeCommand::class, [
      'run',
      '-it',
      '--rm',
      $container,
      $shell,
    ]);
  }
}
