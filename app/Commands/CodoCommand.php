<?php

namespace App\Commands;

use App\Concerns;
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
}
