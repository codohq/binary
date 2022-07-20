<?php

namespace App\Commands\Codo;

use App\Commands;
use App\Commands\CodoCommand;
use function Termwind\{ render };

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
      <div>
        {$this->codo($codo)}

        {$this->dockerVersion()}

        {$this->dockerComposeVersion()}

        {$this->projectConfiguration($codo['config'], $codo['file'])}
      </div>
    HTML);

    if (empty($codo['file'])) {
      render(<<<HTML
        <div class="mb-1 bg-orange-300 text-black px-1 flex justify-center">
          No project found in this directory.
        </div>
      HTML);
    }

    return 0;
  }

  /**
   * Retrieve the version of the current Codo binary.
   *
   * @param  array  $settings
   * @return void
   */
  protected function codo(array $settings): void
  {
    $item = function ($value, $prefix = null) use ($settings) {
      if (! isset($settings[$value]) or empty($settings[$value])) {
        return;
      }

      return <<<HTML
        <div class="w-full text-center px-1">
          {$prefix}
          {$settings[$value]}
        </div>
      HTML;
    };

    render(<<<HTML
      <div class="my-1 px-1 flex justify-center">
        {$item('version', 'Codo')}
        {$item('file')}
      </div>
    HTML);
  }

  /**
   * Retrieve the version of the installed Docker binary.
   *
   * @return void
   */
  protected function dockerVersion(): void
  {
    list ($status, $output) = $this->process('docker --version');

    if ($status !== 0) {
      $this->error($output);
      return;
    }

    preg_match('/(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?/', $output, $matches);

    $version = $matches[0] ?? 'Unknown';

    render(<<<HTML
      <div class="flex justify-between bg-gray-100">
        <span class="px-1">Docker</span>
        <span class="px-1 bg-green-300 text-black">v{$version}</span>
      </div>
    HTML);
  }

  /**
   * Retrieve the version of the installed Docker Compose binary.
   *
   * @return void
   */
  protected function dockerComposeVersion(): void
  {
    list ($status, $output) = $this->process('docker compose version');

    if ($status !== 0) {
      $this->error($output);
      return;
    }

    preg_match('/(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?/', $output, $matches);

    $version = $matches[0] ?? 'Unknown';

    render(<<<HTML
      <div class="flex justify-between">
        <span class="px-1">Docker Compose</span>
        <span class="px-1 bg-green-400 text-black">v{$version}</span>
      </div>
    HTML);
  }

  /**
   *
   *
   * @param  array|null  $config
   * @param  string|null  $file
   * @return void
   */
  protected function projectConfiguration(?array $config, ?string $file): void
  {
    if (is_null($config) or empty($config)) {
      return;
    }

    $items = [
      'Project'             => $config['settings']['name'],
      'Environment'         => $config['settings']['environment'],
      'Project Domain'      => $config['settings']['domain'],
      'Docker Path'         => $config['codo']['components']['docker'],
      'Entrypoint Path'     => $config['codo']['components']['entrypoint'],
      'Framework Path'      => $config['codo']['components']['framework'],
      'Theme Path'          => $config['codo']['components']['theme'],
    ];

    $i = 0;
    foreach ($items as $field => $value) {
      $headerBg = $i % 2 === 0 ? 'bg-gray-100' : '';
      $contentBg = $i % 2 === 0 ? 'bg-green-300' : 'bg-green-400';

      render(<<<HTML
        <div class="flex justify-between {$headerBg}">
          <span class="px-1">{$field}</span>
          <span class="px-1 {$contentBg} text-black">{$value}</span>
        </div>
      HTML);

      $i++;
    }
  }
}
