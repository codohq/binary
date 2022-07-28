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
   * @return string
   */
  protected function projectConfiguration(?Configuration $config): string
  {
    if (is_null($config)) {
      $this->warn('No codo.yml file was found.');

      return '';
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
}
