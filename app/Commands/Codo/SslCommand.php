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
    $certificates = $this->codo['config']->get('network.certificates');

    if (! $certificates?->isNotEmpty()) {
      $this->comment('No certificates were configured to be generated.');

      return 0;
    }

    foreach ($certificates->toArray() as $certificate) {
      $this->createCertificate($certificate['name'], $certificate['hosts']);
    }

    return 0;
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
    $certificate = $this->codo['config']->dockerPath()->asRelative("certificates/{$name}.pem");

    $key = $this->codo['config']->dockerPath()->asRelative("certificates/{$name}-key.pem");

    return $this->call(Services\MkcertCommand::class, [
      '-cert-file',
      $certificate,
      '-key-file',
      $key,
      ...$domains,
    ]);
  }
}
