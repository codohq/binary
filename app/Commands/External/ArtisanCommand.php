<?php

namespace Codohq\Binary\Commands\External;

use Codohq\Binary\Commands;
use function Termwind\{ render };
use Codohq\Binary\Commands\Command;

class ArtisanCommand extends Command
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'artisan {--c|--container=php}';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'Laravel Artisan wrapper command.';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $container = $this->option('container');

    return $this->call(Commands\External\DockerComposeCommand::class, [
      'run',
      '--rm',
      '--interactive',
      '--tty',
      $container,
      './artisan',
    ]);
  }
}
