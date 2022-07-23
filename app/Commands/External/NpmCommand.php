<?php

namespace Codo\Binary\Commands\External;

use function Termwind\{ render };
use Codo\Binary\Commands\CodoCommand;

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
    $codo = app('codo');

    if (empty($codo['file'])) {
      return 1;
    }

    $package = $this->locatePackageJsonFile(getcwd());

    var_dump([getcwd(), $package]);
    exit;

    $workdir = $this->option('workdir') ?? realpath(dirname($codo['file']).'/'.$codo['config']['codo']['components']['theme']);

    list ($status, $output) = $this->runningProcess('npm', $this->getArgv(), $workdir);

    if ($status !== 0) {
      $this->error($output);

      return $status;
    }

    render(<<<HTML
      <div>
        <pre class="w-full px-1">{$output}</pre>
      </div>
    HTML);

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
