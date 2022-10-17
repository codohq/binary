<?php

namespace Codohq\Binary\Exceptions;

use Exception;

class CodoProjectIsDownException extends Exception
{
  /**
   * Holds the exception message.
   *
   * @var string
   */
  protected $message = 'Run `codo up` to start the Codo project first.';
}
