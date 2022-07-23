<?php

namespace Codo\Binary\Commands\External;

use function Termwind\{ render };
use Codo\Binary\Commands\CodoCommand;

class DockerComposeCommand extends CodoCommand
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'docker:compose';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'Docker Compose wrapper command.';

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
    $codo = app('codo');

    if (empty($codo['file'])) {
      return 1;
    }

    $composeFile = sprintf('%s/docker/docker-compose.yml', dirname($codo['file']));
    $envComposeFile = sprintf('%s/docker/docker-compose.%s.yml', dirname($codo['file']), $codo['config']['settings']['environment']);
    $envFile = sprintf('%s/.env', realpath(dirname($codo['file']).'/'.$codo['config']['codo']['components']['entrypoint']));

    list ($status, $output) = $this->process('docker compose', [
      '--project-name', $codo['config']['settings']['name'],
      '-f', $composeFile,
      '-f', $envComposeFile,
      '--env-file', $envFile,
      ...$this->getArgv(),
    ]);

    if ($status !== 0) {
      $this->error($output);

      return $status;
    }

    $this->processOutput($output);

    return 0;
  }
}
