<?php

namespace Codohq\Binary\Commands;

use Codohq\Binary\Concerns;
use Illuminate\Support\Str;
use Illuminate\Console\Scheduling\Schedule;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Cloner\VarCloner;
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
    $codo = app('codo');

    $workingDirectory = $codo['config']->getWorkingDirectory();

    $filename = basename($file);
    $directory = dirname($file);

    $parent = dirname($directory);

    if (! str_starts_with($directory, $workingDirectory)) {
      return null;
    }

    if (in_array($parent, ['/', '\\', '.'])) {
      return null;
    }

    if (! is_file($file)) {
      return $this->locateFile(sprintf('%s/%s', $parent, $filename));
    }

    return $file;
  }

  /**
   * Generate the docker volume arguments for the given path.
   *
   * @param  string|null  $file  null
   * @return array
   */
  public function dockerVolume(?string $file = null): array
  {
    $path = ! is_null($file) ? dirname($file) : getcwd();

    $temporaryDirectory = '/tmp/codo_'.Str::random(10);

    return [
      '--volume',       "${path}:${temporaryDirectory}",
      '--workdir',      $temporaryDirectory,
    ];
  }
}
