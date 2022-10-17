<?php

namespace Codohq\Binary\Commands\Codo;

use Codohq\Binary\Commands\Command;
use Codohq\Binary\Commands\Services;
use Codohq\Binary\Contracts\Eligible;

class SslCommand extends Command implements Eligible
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
  protected $description = 'Generate self-signed certificates for local development.';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $domains = [
      $domain = $this->codo['config']->getDomain(),
    ];

    if ($this->option('wildcard')) {
      $domains[] = "*.{$domain}";
    }

    var_dump($domains); exit;

    return $this->call(Services\MkcertCommand::class, $domains);
  }
}
