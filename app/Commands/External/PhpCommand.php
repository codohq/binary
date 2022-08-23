<?php

namespace Codohq\Binary\Commands\External;

use Codohq\Binary\Commands;
use function Termwind\{ render };
use Codohq\Binary\Commands\Command;

class PhpCommand extends Command
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'php {--c|--container=php}';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'PHP wrapper command.';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $container = $this->option('container');

    $volume = $this->dockerVolume(
      $this->locateFile(getcwd().'/composer.json')
    );

    return $this->call(Commands\External\DockerComposeCommand::class, array_filter([
      'run',
      '--rm',
      '--interactive',
      '--tty',
      ...$volume,
      $container,
    ]));
  }
}
