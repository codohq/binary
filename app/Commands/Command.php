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
   * Holds the output status.
   *
   * @var boolean
   */
  public static bool $showOutput = true;

  /**
   * Holds the CLI argv array.
   *
   * @var array|null
   */
  public static ?array $argv = null;

  /**
   * Holds the current Codo project configuration.
   *
   * @var array
   */
  public array $codo;

  /**
   * Holds the command executor instance.
   *
   * @var \Codohq\Binary\Intermediary;
   */
  public Intermediary $binary;

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

    if (is_null(static::$argv)) {
      static::enableArgv();
    }

    $this->codo = resolve('codo');
    $this->binary = new Intermediary($this);
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
   * Call another console command.
   *
   * @param  \Symfony\Component\Console\Command\Command|string  $command
   * @param  array  $arguments
   * @return int
   */
  public function call($command, array $arguments = [])
  {
    static::enableOutput();
    static::disableArgv();

    $response = parent::call($command, $arguments);

    static::enableArgv();
    static::disableOutput();

    return $response;
  }

  /**
   * Call another console command without output.
   *
   * @param  \Symfony\Component\Console\Command\Command|string  $command
   * @param  array  $arguments
   * @return int
   */
  public function callSilent($command, array $arguments = [])
  {
    static::disableOutput();
    static::disableArgv();

    $response = parent::callSilent($command, $arguments);

    static::enableArgv();
    static::enableOutput();

    return $response;
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
    $workingDirectory = $this->codo['config']->root()->asAbsolute();

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

    $tokens = invade(new ArgvInput(static::$argv))->tokens;

    if ($this->input instanceof ArrayInput) {
      $parameters = invade($this->input)->parameters;

      if (isset($parameters['command']) and class_exists($parameters['command'])) {
        unset($parameters['command']);
      } else if (isset($parameters[0]) and $parameters[0] === $this->input->getFirstArgument()) {
        unset($parameters[0]);
      }

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

  /**
   * Disable the leftovers via argv.
   *
   * @return void
   */
  public static function disableArgv(): void
  {
    static::$argv = [];
  }

  /**
   * Enable the leftovers via argv.
   *
   * @return void
   */
  public static function enableArgv(): void
  {
    static::$argv = $_SERVER['argv'];
  }

  /**
   * Disable process output.
   *
   * @return void
   */
  public static function disableOutput(): void
  {
    static::$showOutput = false;
  }

  /**
   * Enable process output.
   *
   * @return void
   */
  public static function enableOutput(): void
  {
    static::$showOutput = true;
  }

  /**
   * Check if process output is enabled or not.
   *
   * @return boolean
   */
  public static function showOutput(): bool
  {
    return static::$showOutput;
  }
}
