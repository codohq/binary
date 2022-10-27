<?php

namespace Codohq\Binary\Parsers;

use Throwable;
use RuntimeException;
use Codohq\Binary\Manifests;
use Codohq\Binary\Transformers\Variables;
use Codohq\Binary\Contracts\{ ConfigParser, Manifest };

class YamlConfigParser implements ConfigParser
{
  /**
   * Parse the given configuration file.
   *
   * @param  string  $filepath
   * @return \Codohq\Binary\Contracts\Manifest
   */
  public function parse(string $filepath): Manifest
  {
    $yaml = yaml_parse_file($filepath, 0, $ndocs, [
      '!codo' => [$this, 'handleCodoTags'],
      '!cert' => [$this, 'handleCertificateTags'],
    ]);

    $manifest = (float) $yaml['version'];

    switch ($manifest) {
      case 1:
        return Manifests\Version_1::validate($yaml, dirname($filepath));

      default:
        throw new RuntimeException('Invalid manifest version (valid options are "1").');
    }
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

  /**
   * Handle `!cert` tags in the configuration yaml file.
   *
   * @param  mixed  $value
   * @param  string  $tag
   * @param  integer  $flags
   * @return string
   */
  protected function handleCertificateTags(mixed $value, string $tag, int $flags): string
  {
    return serialize(
      new Variables\CertificateVariable($value['public'], $value['private'])
    );
  }
}
