<?php

namespace Codo\Binary\Commands\Codo;

use Codo\Binary\Commands;
use function Termwind\{ render };
use Codo\Binary\Commands\CodoCommand;

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

    $docker = $this->dockerVersion();
    $dockerCompose = $this->dockerComposeVersion();
    $workdir = dirname($codo['file']);

    render(<<<HTML
      <div class="mx-2 my-1">
        <div class="space-x-1">
          <span class="px-1 bg-black font-bold text-white uppercase">Codo</span>
          <span>{$codo['version']}</span>
        </div>
        <div class="mt-1">
          <div class="flex space-x-1">
            <span class="font-bold">Docker</span>
            <span class="flex-1 content-repeat-[.] text-gray"></span>
            <span class="font-bold text-green uppercase">{$docker}</span>
          </div>
          <div class="flex space-x-1">
            <span class="font-bold">Docker Compose</span>
            <span class="flex-1 content-repeat-[.] text-gray"></span>
            <span class="font-bold text-green uppercase">{$dockerCompose}</span>
          </div>
          <div class="flex space-x-1">
            <span class="font-bold">Working Directory</span>
            <span class="flex-1 content-repeat-[.] text-gray"></span>
            <span class="font-bold text-green uppercase">{$workdir}</span>
          </div>
        </div>
      </div>
    HTML);

    // render(<<<HTML
    //   <div>
    //     {$this->codo($codo)}

    //     {$this->dockerVersion()}

    //     {$this->dockerComposeVersion()}

    //     {$this->projectConfiguration($codo['config'], $codo['file'])}
    //   </div>
    // HTML);

    // if (empty($codo['file'])) {
    //   render(<<<HTML
    //     <div class="mb-1 bg-orange-300 text-black px-1 flex justify-center">
    //       No project found in this directory.
    //     </div>
    //   HTML);
    // }

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
   * @return string|null
   */
  protected function dockerVersion(): ?string
  {
    list ($status, $output) = $this->process('docker --version');

    if ($status !== 0) {
      return $this->error($output);
    }

    preg_match('/(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?/', $output, $matches);

    $version = $matches[0] ?? 'Unknown';

    // render(<<<HTML
    //   <div class="flex justify-between bg-gray-100">
    //     <span class="px-1">Docker</span>
    //     <span class="px-1 bg-green-300 text-black">v{$version}</span>
    //   </div>
    // HTML);

    return $version;
  }

  /**
   * Retrieve the version of the installed Docker Compose binary.
   *
   * @return string|null
   */
  protected function dockerComposeVersion(): ?string
  {
    list ($status, $output) = $this->process('docker compose version');

    if ($status !== 0) {
      return $this->error($output);
    }

    preg_match('/(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?/', $output, $matches);

    $version = $matches[0] ?? 'Unknown';

    // render(<<<HTML
    //   <div class="flex justify-between">
    //     <span class="px-1">Docker Compose</span>
    //     <span class="px-1 bg-green-400 text-black">v{$version}</span>
    //   </div>
    // HTML);

    return $version;
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
