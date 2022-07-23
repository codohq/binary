<?php

namespace Codohq\Binary\Commands\External;

use Illuminate\Support\Arr;
use function Termwind\{ render };
use Codohq\Binary\Commands\CodoCommand;

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
    if ($this->isIneligible()) {
      return $this->ineligible();
    }

    $codo = app('codo');

    $composeFiles = Arr::collapse(array_map(fn ($x) => ['-f', $x], array_filter([
      $codo['config']->getDocker('docker-compose.yml', false),
      $codo['config']->getDocker(sprintf('docker-compose.%s.yml', $codo['config']->getEnvironment()), false),
    ])));

    $arguments = [
      '--project-name',
      $codo['config']->getProject(),

      '--env-file',
      $codo['config']->getEntrypoint('.env', true),

      ...$composeFiles,

      ...$this->getArgv(),
    ];

    list ($status, $output) = $this->process('docker compose', $arguments);

    if ($status !== 0) {
      $this->error($output);

      return $status;
    }

    $this->info($output);

    return 0;
  }
}
