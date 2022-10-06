<?php

namespace Codohq\Binary\Commands\Codo;

use Codohq\Binary\Commands;
use Codohq\Binary\Commands\Command;
use Codohq\Binary\Services\{ Docker, DockerCompose };

class TraefikCommand extends Command
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'proxy';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'Start a global Traefik proxy container.';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $process = (new Docker)->onRaw([
      'docker',
      'network',
      'create',
      'codo_traefik',
    ], app_path('Docker'));

    $process->run();

    $process = (new DockerCompose)->onRaw([
      'docker',
      'compose',
      '-f', app_path('Docker/traefik.yml'),
      'up',
      '--force-recreate',
      '--remove-orphans',
      '--build',
    ], app_path('Docker'));

    $process->run();

    return $process->getExitCode();
  }
}
