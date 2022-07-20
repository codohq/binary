<?php

namespace App\Commands\External;

use App\Commands\CodoCommand;
use function Termwind\{ render };

class NpmCommand extends CodoCommand
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'npm';

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

    $workdir = realpath(dirname($codo['file']).'/'.$codo['config']['codo']['components']['theme']);

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
}
