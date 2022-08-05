<?php

namespace Codohq\Binary\Input;

use RuntimeException;
use Symfony\Component\Console\Input\ArgvInput as Base;

class ArgvInput extends Base
{
  /**
   * Holds all of the external arguments.
   *
   * @var array
   */
  protected array $externalArguments = [];

  /**
   * Retrieves all of the external arguments.
   *
   * @return array
   */
  public function getExternalArguments(): array
  {
    return $this->externalArguments;
  }

  /**
   * {@inheritdoc}
   */
  protected function parseToken(string $token, bool $parseOptions): bool
  {
    try {
      return parent::parseToken($token, $parseOptions);
    } catch (RuntimeException $e) {
      $this->externalArguments[] = $token;

      return true;
    }
  }
}
