<?php

namespace Codohq\Binary\Parsers;

use Codohq\Binary\Configuration;
use Codohq\Binary\Contracts\ConfigParser;

class YamlConfigParser implements ConfigParser
{
  /**
   * Parse the given configuration file.
   *
   * @param  string  $filepath
   * @return \Codohq\Binary\Configuration
   */
  public function parse(string $filepath): Configuration
  {
    $yaml = yaml_parse_file($filepath, 0, $ndocs, [
      '!codo' => [$this, 'handleCodoTags'],
    ]);

    return new Configuration($yaml, dirname($filepath));
  }

  /**
   * Handle `!codo` tags in the configuration yaml file.
   *
   * @param  mixed  $value
   * @param  string  $tag
   * @param  integer  $flags
   * @return mixed
   */
  protected function handleCodoTags(mixed $value, string $tag, int $flags): mixed
  {
    return match(strtolower($value)) {
      'version' => config('app.version'),
      default   => null,
    };
  }
}
