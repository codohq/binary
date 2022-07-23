<?php

namespace Codohq\Binary\Commands\Codo;

use Codohq\Binary\Commands;
use Illuminate\Support\Arr;
use Codohq\Binary\Configuration;
use function Termwind\{ render };
use Codohq\Binary\Commands\CodoCommand;

class InfoCommand extends CodoCommand
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
      <div class="mx-2 my-1">
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
    return $this->renderItems([
      'Prerequisites'   => [null, [
        'Docker'          => $this->dockerVersion(),
        'Docker Compose'  => $this->dockerComposeVersion(),
      ]],
    ]);
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

    return $this->renderItems([
      'Project'         => [null, [
        'Name'            => $config->getProject(),
        'Environment'     => $config->getEnvironment(),
        'Domain'          => $config->getDomain(),
        'Path'            => [$config->getWorkingDirectory(), [
          'Docker'          => $config->getDocker(),
          'Entrypoint'      => $config->getEntrypoint(),
          'Framework'       => $config->getFramework(),
          'Theme'           => $config->getTheme(),
          'Commands'        => [null, $config->getCommandDirectories(false)],
        ]],
      ]],
    ]);
  }

  /**
   * Render a list of items.
   *
   * @param  array  $items
   * @param  integer  $depth
   * @return string
   */
  protected function renderItems(array $items, int $depth = 0): string
  {
    $content = '';

    foreach ($items as $field => $value) {
      list ($value, $children) = array_pad(Arr::wrap($value), 2, null);

      $padding = (int) $depth * 2;
      $classes = $depth !== 0 ? "pl-{$padding}" : '';
      $prefix = $depth > 0 ? '<span class="text-gray">↳</span> ' : '';

      $html = is_null($value) ? '' : <<<HTML
        <span class="flex-1 content-repeat-[.] text-gray"></span>
        <span class="font-bold text-blue">{$value}</span>
      HTML;

      if (is_null($children) or ! empty($children)) {
        $content .= <<<HTML
          <div class="flex space-x-1 {$classes}">
            <span class="font-bold">{$prefix}{$field}</span>
            {$html}
          </div>
        HTML;
      }

      if (! empty($children)) {
        $depth++;
        $content .= $this->renderItems((array) $children, $depth);
      }
    }

    return <<<HTML
      <div>{$content}</div>
    HTML;
  }

  /**
   * Retrieve the version of the installed Docker binary.
   *
   * @return string|null
   */
  protected function dockerVersion(): ?string
  {
    list ($status, $output) = $this->silentProcess('docker --version');

    if ($status !== 0) {
      return $this->error($output);
    }

    preg_match('/(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?/', $output, $matches);

    $version = $matches[0] ?? false;

    return $version ? "v{$version}" : 'Unknown';
  }

  /**
   * Retrieve the version of the installed Docker Compose binary.
   *
   * @return string|null
   */
  protected function dockerComposeVersion(): ?string
  {
    list ($status, $output) = $this->silentProcess('docker compose version');

    if ($status !== 0) {
      return $this->error($output);
    }

    preg_match('/(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?/', $output, $matches);

    $version = $matches[0] ?? false;

    return $version ? "v{$version}" : 'Unknown';
  }
}
