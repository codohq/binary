<?php

namespace Codohq\Binary\Commands;

use Codohq\Binary\Concerns;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command as Base;

abstract class CodoCommand extends Base
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
}
