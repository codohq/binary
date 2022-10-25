<?php

namespace Codohq\Binary\Contracts;

interface ConfigParser
{
  /**
   * Parse the given configuration file.
   *
   * @param  string  $filepath
   * @return \Codohq\Binary\Contracts\Manifest
   */
  public function parse(string $filepath): Manifest;
}
