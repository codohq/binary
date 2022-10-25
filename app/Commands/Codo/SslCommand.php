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
    var_dump('SSL generation!'); exit;

    $domains = [
      $domain = $this->codo['config']->getDomain(),
      ...$this->codo['config']->getSubdomains(),
    ];

    if ($this->option('wildcard')) {
      $domains[] = "*.{$domain}";
    }

    $main = $this->createCertificate($domain, $domains);

    foreach ($this->codo['config']->getExtraCertificates() as $extraName => $extraDomains) {
      $this->createCertificate($extraName, $extraDomains);
    }

    return $main;
  }

  /**
   * Generate a certificate.
   *
   * @param  string  $name
   * @param  array  $domains
   * @return integer
   */
  protected function createCertificate(string $name, array $domains): int
  {
    $certificate = $this->codo['config']->getDocker("certificates/{$name}.pem");

    $key = $this->codo['config']->getDocker("certificates/{$name}-key.pem");

    return $this->call(Services\MkcertCommand::class, [
      '-cert-file',
      $certificate,
      '-key-file',
      $key,
      ...$domains,
    ]);
  }
}
