<?php

namespace Codohq\Binary\Contracts;

use Codohq\Binary\Configuration;

interface ConfigParser
{
  /**
   * Parse the given configuration file.
   *
   * @param  string  $filepath
   * @return \Codohq\Binary\Configuration
   */
  public function parse(string $filepath): Configuration;
}
