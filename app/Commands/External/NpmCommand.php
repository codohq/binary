<?php

namespace Codohq\Binary\Commands\External;

use function Termwind\{ render };
use Codohq\Binary\Commands\CodoCommand;

class NpmCommand extends CodoCommand
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'npm {--w|workdir=}';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'NPM wrapper command.';

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

    $package = $this->locatePackageJsonFile(getcwd());

    $workdir = $this->option('workdir')
      ?? ($package ? dirname($package) : $codo['config']->getTheme(null, true));

    list ($status, $output) = $this->runningProcess('npm', $this->getArgv(), $workdir);

    if (! empty($output)) {
      if (! empty($status)) {
        $this->error($output);

        return $status;
      }

      $this->info($output);
    }

    return 0;
  }

  /**
   * Retrieve the absolute path to the closest package.json file.
   *
   * @param  string  $directory
   * @return string|null
   */
  protected function locatePackageJsonFile(string $directory): ?string
  {
    $expected = sprintf('%s/package.json', $directory);

    $parent = dirname($directory);

    if (in_array($parent, ['/', '\\', '.'])) {
      return null;
    }

    if (! is_file($expected)) {
      return $this->locatePackageJsonFile($parent);
    }

    return $expected;
  }
}
