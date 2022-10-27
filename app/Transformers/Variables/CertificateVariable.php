<?php

namespace Codohq\Binary\Transformers\Variables;

use Codohq\Binary\Contracts\VariableTransformer as Contract;

class CertificateVariable implements Contract
{
  /**
   * Instantiate a new certificate variable transformer object.
   *
   * @param  string  $public
   * @param  string  $private
   * @return void
   */
  public function __construct(protected string $public, protected string $private)
  {
    //
  }

  /**
   * Handle the transformation.
   *
   * @param  string  $variable
   * @return mixed
   */
  public function handle(string $variable): mixed
  {
    return [
      "{$variable}_PUBLIC"  => $this->public,
      "{$variable}_PRIVATE" => $this->private,
    ];
  }
}
