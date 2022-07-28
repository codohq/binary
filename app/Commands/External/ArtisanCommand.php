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
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();

    $this->ignoreValidationErrors();
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    return $this->callWithArgv(Commands\External\DockerComposeCommand::class, [
      'run',
      '--rm',
      '--interactive',
      '--tty',
      'php',
      './artisan',
    ]);
  }
}
