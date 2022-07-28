<?php

namespace Codohq\Binary\Commands;

use Codohq\Binary\Concerns;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command as Base;

abstract class Command extends Base
{
  use Concerns\InteractsWithArgv;
  use Concerns\InteractsWithOutput;

  /**
   * Define the command's schedule.
   *
   * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
   * @return void
   */
  public function schedule(Schedule $schedule)
  {
    // $schedule->command(static::class)->everyMinute();
  }

  /**
   * Check if the current working directory is eligible or not.
   *
   * @return boolean
   */
  public function isEligible(): bool
  {
    $codo = app('codo');

    return ! empty($codo['file']) and ! empty($codo['config']);
  }

  /**
   * Check if the current working directory is ineligible or not.
   *
   * @return boolean
   */
  public function isIneligible(): bool
  {
    return ! $this->isEligible();
  }

  /**
   * Retrieve the absolute path to the closest package.json file.
   *
   * @param  string  $file
   * @return string|null
   */
  public function locateFile(string $file): ?string
  {
    $filename = basename($file);
    $directory = dirname($file);

    $parent = dirname($directory);

    if (in_array($parent, ['/', '\\', '.'])) {
      return null;
    }

    if (! is_file($file)) {
      return $this->locateFile(sprintf('%s/%s', $parent, $filename));
    }

    return $file;
  }
}