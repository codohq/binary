<?php

namespace Codohq\Binary\Exceptions;

use Exception;

class IneligibleException extends Exception
{
  /**
   * Holds the exception message.
   *
   * @var string
   */
  protected $message = 'Unable to find a `codo.yml` file.';
}
