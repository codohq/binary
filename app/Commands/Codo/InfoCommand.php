<?php

namespace Codohq\Binary\Commands\Codo;

use Illuminate\Support\Arr;
use function Termwind\{ render };
use Codohq\Binary\Commands\Command;
use Codohq\Binary\Components\GroupedList;
use Codohq\Binary\{ Commands, Contracts, Binaries };

class InfoCommand extends Command
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = '
    info
    {--e|--env-variables}
  ';

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

    $variables = $this->option('env-variables') ? $this->envVariables($codo['config']) : null;

    render(<<<HTML
      <div class="my-1">
        <div class="space-x-1">
          <span class="px-1 bg-black font-bold text-white uppercase">Codo</span>
          <span>{$codo['version']}</span>
        </div>
        <div class="mt-1 space-y-1">
          {$this->prerequisites()}
          {$this->projectConfiguration($codo['config'])}
          {$variables}
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
    $items = (new GroupedList)
      ->addGroup('Prerequisites', function ($group) {
        $group->addItem('Docker', Binaries\Docker::version(), 'renderVersion');
        $group->addItem('Docker Compose', Binaries\DockerCompose::version(), 'renderVersion');
        $group->addItem('Mkcert (Optional)', Binaries\Mkcert::version(), 'renderVersion');
      });

    return $items->render();
  }

  /**
   * Render a list of the project configuration
   *
   * @param  \Codohq\Binary\Contracts\Manifest|null  $config
   * @return string|null
   */
  protected function projectConfiguration(?Contracts\Manifest $config): ?string
  {
    if ($this->isIneligible()) {
      return $this->ineligible(render: false);
    }

    $items = (new GroupedList)
      ->addGroup('Project', function ($group) use ($config) {
        $group->addItem('Name', $config->name(), 'renderProject');
        $group->addItem('Environment', $config->environment(), 'renderValue');
        $group->addItem('Domain', $config->get('network.hostname'), 'renderValue');

        $group->addGroup('Paths', function ($group) use ($config) {
          $group->addItem('Root', $config->root()->asAbsolute(), 'renderValue');
          $group->addItem('Docker', $config->dockerPath()->asRelative(), 'renderValue');
          $group->addItem('Entrypoint', $config->entrypoint()->asRelative(), 'renderValue');

          $group->addGroup('Commands', function ($group) use ($config) {
            $group->addItems($config->get('commands')?->toArray(), 'renderValue');
          });
        });
      });

    return $items->render();
  }

  /**
   * Render a list of the project's environment variables.
   *
   * @param  \Codohq\Binary\Contracts\Manifest|null  $config
   * @return string|null
   */
  protected function envVariables(?Contracts\Manifest $config): ?string
  {
    if ($this->isIneligible()) {
      return $this->ineligible(render: false);
    }

    $envVariables = $config->environmentVariables();

    $items = (new GroupedList)
      ->addGroup('', function ($group) use ($envVariables) {
        $group->addGroup('Environment Variables', function ($group) use ($envVariables) {
          foreach ($envVariables as $variable => $value) {
            $group->addItem($variable, $value, 'renderValue');
          }
        });
      });

    return $items->render();
  }
}
