<?php

namespace Codohq\Binary\Commands\Codo;

use Illuminate\Support\Arr;
use function Termwind\{ render };
use Codohq\Binary\Commands\Command;
use Codohq\Binary\Components\ListOfItems;
use Codohq\Binary\{ Commands, Configuration };

class InfoCommand extends Command
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'info';

  /**
   * The description of the command.
   *
   * @var string
   */
  protected $description = 'See information about Codo and the environment.';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $codo = app('codo');

    render(<<<HTML
      <div class="my-1">
        <div class="space-x-1">
          <span class="px-1 bg-black font-bold text-white uppercase">Codo</span>
          <span>{$codo['version']}</span>
        </div>
        <div class="mt-1 space-y-1">
          {$this->prerequisites()}
          {$this->projectConfiguration($codo['config'])}
        </div>
      </div>
    HTML);

    return 0;
  }

  /**
   * Render a list of the prerequisites.
   *
   * @return string
   */
  protected function prerequisites(): string
  {
    $items = (new ListOfItems)
      ->addGroup('Prerequisites', function ($group) {
        $group->addItem('Docker', $this->dockerVersion(), [$this, 'renderVersion']);
        $group->addItem('Docker Compose', $this->dockerComposeVersion(), [$this, 'renderVersion']);
      });

    return $this->render($items);
  }

  /**
   * Render a list of the project configuration
   *
   * @param  \Codohq\Binary\Configuration|null  $config
   * @return string
   */
  protected function projectConfiguration(?Configuration $config): string
  {
    if (is_null($config)) {
      $this->warn('No codo.yml file was found.');

      return '';
    }

    $items = (new ListOfItems)
      ->addGroup('Project', function ($group) use ($config) {
        $group->addItem('Name', $config->getProject(), [$this, 'renderProject']);
        $group->addItem('Environment', $config->getEnvironment(), [$this, 'renderValue']);
        $group->addItem('Domain', $config->getDomain(), [$this, 'renderValue']);

        $group->addGroup('Paths', function ($group) use ($config) {
          $group->addItem('Root', $config->getWorkingDirectory(), [$this, 'renderValue']);
          $group->addItem('Docker', $config->getDocker(), [$this, 'renderValue']);
          $group->addItem('Entrypoint', $config->getEntrypoint(), [$this, 'renderValue']);
          $group->addItem('Framework', $config->getFramework(), [$this, 'renderValue']);
          $group->addItem('Theme', $config->getTheme(), [$this, 'renderValue']);

          $group->addGroup('Commands', function ($group) use ($config) {
            $group->addItems($config->getCommandDirectories(false), [$this, 'renderValue']);
          });
        });
      });

    return $this->render($items);
  }

  /**
   * Render a list of items.
   *
   * @param  \Codohq\Binary\Components\ListOfItems  $items
   * @param  integer  $depth
   * @param  string  $color  blue
   * @return string
   */
  protected function render(ListOfItems $items, int $depth = 0): string
  {
    $content = '';

    foreach ($items->toArray() as $item) {
      $padding = (int) $depth * 2;

      $classes = $depth !== 0 ? "pl-{$padding}" : '';

      $prefix = $depth > 0 ? '<span class="text-gray">↳</span> ' : '';

      if ($item instanceof ListOfItems) {
        $content .= $this->renderGroup($item, $classes, $prefix, $depth);
        continue;
      }

      $content .= $this->renderLine($item, $classes, $prefix);
    }

    return <<<HTML
      <div>{$content}</div>
    HTML;
  }

  /**
   * Render the project name.
   *
   * @param  \Codohq\Binary\Components\ListOfItems  $group
   * @param  string  $classes
   * @param  string  $prefix
   * @param  integer  $depth
   * @return string
   */
  public function renderGroup(ListOfItems $group, string $classes, string $prefix, int $depth): string
  {
    $content = '';

    $value = $group->getValue() ? <<<HTML
      <span class="flex-1 content-repeat-[.] text-gray"></span>
      <span class="font-bold text-blue">{$group->getValue()}</span>
    HTML : '';

    $content .= <<<HTML
      <div class="flex space-x-1 {$classes}">
        <span class="font-bold">{$prefix}{$group->getHeading()}</span>
        {$value}
      </div>
    HTML;

    $content .= $this->render($group, $depth + 1);

    return $content;
  }

  /**
   * Render an item wrapper.
   *
   * @param  array  $item
   * @param  string  $classes
   * @param  string  $prefix
   * @return string
   */
  public function renderLine(array $item, string $classes, string $prefix): string
  {
    return <<<HTML
      <div class="flex space-x-1 {$classes}">
        <span class="font-bold">{$prefix}{$item['heading']}</span>
        <span class="flex-1 content-repeat-[.] text-gray"></span>
        {$item['value']}
      </div>
    HTML;
  }

  /**
   * Render the project name.
   *
   * @param  mixed  $value
   * @return string
   */
  public function renderProject(mixed $value): string
  {
    return <<<HTML
      <span class="bg-yellow px-1 font-bold">{$value}</span>
    HTML;
  }

  /**
   * Render an item value.
   *
   * @param  mixed  $value
   * @return string
   */
  public function renderValue(mixed $value): string
  {
    return <<<HTML
      <span class="text-blue font-bold">{$value}</span>
    HTML;
  }

  /**
   * Render the version constraints.
   *
   * @param  mixed  $value
   * @return string
   */
  public function renderVersion(mixed $value): string
  {
    $success = <<<HTML
      <span class="text-green font-bold">v{$value}</span>
    HTML;

    $failure = <<<HTML
      <span class="bg-red px-1 font-bold text-white">Missing</span>
    HTML;

    return $value !== false ? $success : $failure;
  }

  /**
   * Retrieve the version of the installed Docker binary.
   *
   * @return string|boolean
   */
  protected function dockerVersion(): string|bool
  {
    $output = trim(shell_exec('docker --version'));

    preg_match('/(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?/', $output, $matches);

    return $matches[0] ?? false;
  }

  /**
   * Retrieve the version of the installed Docker Compose binary.
   *
   * @return string|boolean
   */
  protected function dockerComposeVersion(): string|bool
  {
    $output = trim(shell_exec('docker compose version'));

    preg_match('/(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?/', $output, $matches);

    return $matches[0] ?? false;
  }
}
