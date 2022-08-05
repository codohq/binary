<?php

namespace Codohq\Binary\Commands\Codo;

use Codohq\Binary\Commands;
use Codohq\Binary\Commands\Command;

class GenerateCertificatesCommand extends Command
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'ssl {--w|--wildcard}';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'Generate the self-signed certificates for local development.';

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

    return $this->call(Commands\External\MkcertCommand::class, array_filter([
      $codo['config']->getDomain(),
      $this->option('wildcard') ? '*.'.$codo['config']->getDomain() : null,
    ]));
  }
}
