<?php

namespace Codohq\Binary\Contracts;

use Codohq\Binary\PathObject;

interface VariableTransformer
{
  /**
   * Handle the transformation.
   *
   * @param  string  $variable
   * @return mixed
   */
  public function handle(string $variable): mixed;
}
