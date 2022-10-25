<?php

namespace Codohq\Binary;

use Codohq\Binary\CliString;
use Codohq\Binary\Commands\Command;
use Illuminate\Support\Facades\Artisan;

if (! function_exists('codo'))
{
  /**
   * Call a Codo command using a shell commandline string.
   *
   * @param  string  $commandline
   * @param  boolean  $silent  false
   * @return integer
   */
  function codo(string $commandline, bool $silent = false): int
  {
    Command::enableOutput();

    $arguments = (new CliString($commandline))->toArray();

    $command = array_shift($arguments);

    if ($silent) {
      Command::disableOutput();
    }

    $exitCode = Artisan::call($command, $arguments);

    Command::enableOutput();

    return $exitCode;
  }
}

if (! function_exists('option'))
{
  /**
   * Create a string-based option with a value if one was given.
   *
   * @param  string  $option
   * @param  mixed  $value
   * @return string
   */
  function option(string $option, mixed $value): string
  {
    if (empty($value)) {
      return '';
    }

    return implode(' ', [$option, $value]);
  }
}
