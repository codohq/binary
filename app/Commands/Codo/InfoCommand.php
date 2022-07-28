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
        $group->addItem('Docker', $this->dockerVersion());
        $group->addItem('Docker Compose', $this->dockerComposeVersion());
      });

    return $this->renderItems($items, 0, 'green');
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
        $group->addItem('Name', $config->getProject());
        $group->addItem('Environment', $config->getEnvironment());
        $group->addItem('Domain', $config->getDomain());

        $group->addGroup('Paths', function ($group) use ($config) {
          $group->addItem('Base', $config->getWorkingDirectory());
          $group->addItem('Docker', $config->getDocker());
          $group->addItem('Entrypoint', $config->getEntrypoint());
          $group->addItem('Framework', $config->getFramework());
          $group->addItem('Theme', $config->getTheme());

          $group->addGroup('Commands', function ($group) use ($config) {
            $group->addItems($config->getCommandDirectories(false));
          });
        });
      });

    return $this->renderItems($items);
  }

  /**
   * Render a list of items.
   *
   * @param  \Codohq\Binary\Components\ListOfItems  $items
   * @param  integer  $depth
   * @param  string  $color  blue
   * @return string
   */
  protected function renderItems(ListOfItems $items, int $depth = 0, $color = 'blue'): string
  {
    $content = '';

    foreach ($items->toArray() as $item) {
      $padding = (int) $depth * 2;
      $classes = $depth !== 0 ? "pl-{$padding}" : '';
      $prefix = $depth > 0 ? '<span class="text-gray">↳</span> ' : '';

      if ($item instanceof ListOfItems) {
        $value = $item->getValue() ? <<<HTML
          <span class="flex-1 content-repeat-[.] text-gray"></span>
          <span class="font-bold text-{$color}">{$item->getValue()}</span>
        HTML : '';

        $content .= <<<HTML
          <div class="flex space-x-1 {$classes}">
            <span class="font-bold">{$prefix}{$item->getHeading()}</span>
            {$value}
          </div>
        HTML;

        $content .= $this->renderItems($item, $depth + 1);

        continue;
      }

      $value = $item['value'] !== false ? <<<HTML
        <span class="font-bold text-{$color}">{$item['value']}</span>
      HTML : <<<HTML
        <span class="font-bold text-red">N/A</span>
      HTML;

      $content .= <<<HTML
        <div class="flex space-x-1 {$classes}">
          <span class="font-bold">{$prefix}{$item['heading']}</span>
          <span class="flex-1 content-repeat-[.] text-gray"></span>
          {$value}
        </div>
      HTML;
    }

    return <<<HTML
      <div>{$content}</div>
    HTML;
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

    $version = $matches[0] ?? false;

    return $version ? "v{$version}" : false;
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

    $version = $matches[0] ?? false;

    return $version ? "v{$version}" : false;
  }
}
