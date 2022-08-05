<?php

namespace Codohq\Binary\Commands;

use Codohq\Binary\Concerns;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command as Base;

abstract class Command extends Base
{
  use Concerns\CallsCommands;
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
   * Dump output using Symfony's CLI dumper.
   *
   * @param  mixed  ...$arguments
   * @return void
   */
  protected function dump(...$arguments): void
  {
    $cloner = new VarCloner;

    $dumper = new CliDumper;

    foreach ($arguments as $argument) {
      $dumper->dump($cloner->cloneVar($argument));
    }

    die(1);
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
