<?php

namespace Codohq\Binary\Input;

use RuntimeException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\ArrayInput as Base;

class ArrayInput extends Base
{
  /**
   * Holds all of the external arguments.
   *
   * @var array
   */
  protected array $externalArguments = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $parameters, InputDefinition $definition = null)
  {
    parent::__construct($parameters, $definition);

    $this->externalArguments = array_filter($parameters, 'is_int', ARRAY_FILTER_USE_KEY);
  }

  /**
   * Retrieves all of the external arguments.
   *
   * @return array
   */
  public function getExternalArguments(): array
  {
    return $this->externalArguments;
  }
}
