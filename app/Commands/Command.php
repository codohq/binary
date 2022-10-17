<?php

namespace Codohq\Binary\Commands;

use Codohq\Binary\Concerns;
use Codohq\Binary\Intermediary;
use Codohq\Binary\Contracts\Eligible;
use Illuminate\Console\Scheduling\Schedule;
use Codohq\Binary\Exceptions\IneligibleException;
use LaravelZero\Framework\Commands\Command as Base;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\{ ArrayInput, ArgvInput };

abstract class Command extends Base
{
  use Concerns\InteractsWithOutput;

  /**
   * Holds the command executor instance.
   *
   * @var \Codohq\Binary\Intermediary;
   */
  public Intermediary $binary;

  /**
   * Holds the current Codo project configuration.
   *
   * @var array
   */
  public array $codo;

  /**
   * Holds all of the leftover arguments.
   *
   * @var array|null
   */
  protected ?array $leftovers = null;

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->ignoreValidationErrors();

    parent::__construct();

    $this->codo = resolve('codo');
    $this->binary = new Intermediary($this);
  }

  /**
   * Execute the console command.
   *
   * @param  \Symfony\Component\Console\Input\InputInterface  $input
   * @param  \Symfony\Component\Console\Output\OutputInterface  $output
   * @return int
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    if ($this instanceof Eligible and $this->isIneligible()) {
      throw new IneligibleException;
    }

    return parent::execute($input, $output);
  }

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
  public function recursiveFileSearch(string $file): ?string
  {
    $workingDirectory = $this->codo['config']->getWorkingDirectory();

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
      return $this->recursiveFileSearch(sprintf('%s/%s', $parent, $filename));
    }

    return $file;
  }

  /**
   * Create an input instance from the given arguments.
   *
   * @param  array  $arguments
   * @return \Symfony\Component\Console\Input\ArrayInput
   */
  protected function createInputFromArguments(array $arguments)
  {
    $this->input = parent::createInputFromArguments($arguments);

    return $this->input;
  }

  /**
   * Parse any leftover arguments.
   *
   * @return void
   */
  protected function parseLeftovers(): void
  {
    $definition = $this->getDefinition();

    $tokens = invade(new ArgvInput)->tokens;

    if ($this->input instanceof ArrayInput) {
      $parameters = invade($this->input)->parameters;
      unset($parameters['command']);

      unset($tokens[0]);

      $tokens = array_merge($parameters, $tokens);
    }

    $skipNext = false;
    $separatorFound = false;

    $tokens = array_filter($tokens, function ($token) use ($definition, &$skipNext, &$separatorFound) {
      if ($skipNext) {
        $skipNext = false;

        return false;
      }

      if ($separatorFound or ! str_starts_with($token, '-')) {
        return true;
      }

      if ($token === '--') {
        $separatorFound = true;

        return false;
      }

      if (! $this->input->hasParameterOption($token)) {
        return true;
      }

      $value = $this->input->getParameterOption($token);

      if (str_contains($token, '=')) {
        return false;
      }

      $option = preg_replace('/^([-]+)/', '', $token);

      if (! $definition->hasOption($option) and ! $definition->hasShortcut($option)) {
        return true;
      }

      $skipNext = true;

      return false;
    });

    $arguments = $this->input->getArguments();

    if (! ($this->input instanceof ArrayInput)) {
      // unset($arguments['command']);
      foreach ($arguments as $argument) {
        array_splice($tokens, array_search($argument, $tokens), 1);
      }
    }

    $tokens = array_map(function ($token) {
      if (! str_contains($token, ' ')) {
        return $token;
      }

      return sprintf('"%s"', $token);
    }, $tokens);

    $this->leftovers = array_values($tokens);
  }

  /**
   * Retrieve all of the leftover arguments.
   *
   * @return array
   */
  public function leftovers(): array
  {
    if (is_null($this->leftovers)) {
      $this->parseLeftovers();
    }

    return $this->leftovers;
  }
}
