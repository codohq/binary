<?php

namespace Codohq\Binary\Concerns;

use Closure;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Codohq\Binary\Input\{ ArgvInput, ArrayInput };
use Symfony\Component\Console\Input as SymfonyInput;
use Symfony\Component\Console\Output\OutputInterface;

trait CallsCommands
{
  /**
   * {@inheritdoc}
   */
  public function call($command, array $arguments = [])
  {
    $arguments = array_merge($arguments, $this->getExternalArguments());

    return parent::call($command, $arguments);
  }

  /**
   * {@inheritdoc}
   */
  public function callSilent($command, array $arguments = [])
  {
    $arguments = array_merge($arguments, $this->getExternalArguments());

    return parent::callSilent($command, $arguments);
  }

  /**
   * Transform the input and output interfaces.
   *
   * @param  \Symfony\Component\Console\Input\InputInterface  $input
   * @param  \Symfony\Component\Console\Output\OutputInterface  $output
   * @param  \Closure  $callback
   * @return mixed
   */
  protected function transform(SymfonyInput\InputInterface $input, OutputInterface $output, Closure $callback): mixed
  {
    if ($input instanceof SymfonyInput\ArgvInput) {
      return $callback(new ArgvInput, $output);
    }

    if ($input instanceof SymfonyInput\ArrayInput) {
      $this->ignoreValidationErrors();
    }

    return $callback($input, $output);
  }

  /**
   * Run the console command.
   *
   * @param  \Symfony\Component\Console\Input\InputInterface  $input
   * @param  \Symfony\Component\Console\Output\OutputInterface  $output
   * @return int
   */
  public function run(SymfonyInput\InputInterface $input, OutputInterface $output): int
  {
    return $this->transform($input, $output, function ($input, $output) {
      return parent::run($input, $output);
    });
  }

  /**
   * Execute the console command.
   *
   * @param  \Symfony\Component\Console\Input\InputInterface  $input
   * @param  \Symfony\Component\Console\Output\OutputInterface  $output
   * @return int
   */
  protected function execute(SymfonyInput\InputInterface $input, OutputInterface $output)
  {
    return $this->transform($input, $output, function ($input, $output) {
      return parent::execute($input, $output);
    });
  }

  /**
   * Retrieves all of the external arguments.
   *
   * @return array
   */
  public function getExternalArguments(): array
  {
    return method_exists($this->input, 'getExternalArguments')
      ? $this->input->getExternalArguments()
      : [];
  }

  /**
   * Create an input instance from the given arguments.
   *
   * @param  array  $arguments
   * @return \Symfony\Component\Console\Input\ArrayInput
   */
  protected function createInputFromArguments(array $arguments)
  {
    return tap(new ArrayInput(array_merge($this->context(), $arguments)), function ($input) {
      if ($input->getParameterOption('--no-interaction')) {
        $input->setInteractive(false);
      }
    });
  }
}
