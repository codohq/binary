<?php

namespace Codohq\Binary\Commands\Codo;

use Illuminate\Support\Arr;
use function Termwind\{ render };
use Codohq\Binary\Commands\Command;
use Codohq\Binary\Components\GroupedList;
use Codohq\Binary\{ Commands, Configuration, Services };

class InfoCommand extends Command
{
  /**
   * The signature of the command.
   *
   * @var string
   */
  protected $signature = 'info {--e|--env-variables}';

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
        $group->addItem('Docker', Services\Docker::version(), 'renderVersion');
        $group->addItem('Docker Compose', Services\DockerCompose::version(), 'renderVersion');
      });

    return $items->render();
  }

  /**
   * Render a list of the project configuration
   *
   * @param  \Codohq\Binary\Configuration|null  $config
   * @return string|null
   */
  protected function projectConfiguration(?Configuration $config): ?string
  {
    if ($this->isIneligible()) {
      return $this->ineligible(render: false);
    }

    $items = (new GroupedList)
      ->addGroup('Project', function ($group) use ($config) {
        $group->addItem('Name', $config->getProject(), 'renderProject');
        $group->addItem('Environment', $config->getEnvironment(), 'renderValue');
        $group->addItem('Domain', $config->getDomain(), 'renderValue');

        $group->addGroup('Paths', function ($group) use ($config) {
          $group->addItem('Root', $config->getWorkingDirectory(), 'renderValue');
          $group->addItem('Docker', $config->getDocker(), 'renderValue');
          $group->addItem('Entrypoint', $config->getEntrypoint(), 'renderValue');
          $group->addItem('Framework', $config->getFramework(), 'renderValue');
          $group->addItem('Theme', $config->getTheme(), 'renderValue');

          $group->addGroup('Commands', function ($group) use ($config) {
            $group->addItems($config->getCommandDirectories(false), 'renderValue');
          });
        });
      });

    return $items->render();
  }

  /**
   * Render a list of the project's environment variables.
   *
   * @param  \Codohq\Binary\Configuration|null  $config
   * @return string|null
   */
  protected function envVariables(?Configuration $config): ?string
  {
    if ($this->isIneligible()) {
      return $this->ineligible(render: false);
    }

    $envVariables = $config->getEnvironmentVariables();

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
