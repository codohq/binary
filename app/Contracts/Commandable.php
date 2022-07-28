<?php

namespace Codohq\Binary\Contracts;

use Illuminate\Contracts\Support\Arrayable;

interface Commandable extends Arrayable
{
  /**
   * Retrieve the working directory for the command.
   * 
   * @return string|null
   */
  public function workspace(): ?string;

  /**
   * Retrieve the environment variables for which the command is run with.
   * 
   * @return array
   */
  public function environment(): array;
}
