<?php

namespace Codohq\Binary\Providers;

use Phar;
use ReflectionClass;
use Illuminate\Console\Command;
use Codohq\Binary\Configuration;
use Symfony\Component\Finder\Finder;
use Illuminate\Contracts\Console\Kernel;
use Codohq\Binary\Parsers\YamlConfigParser;
use Illuminate\Console\Application as Artisan;
use Illuminate\Support\{ Arr, Str, Collection, ServiceProvider };

class AppServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    //
  }

  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    $this->registerHelpers();
    $this->initialiseCodo();
  }

  /**
   * Register helper functions.
   *
   * @return void
   */
  protected function registerHelpers(): void
  {
    Collection::macro('path', function ($key, $default = null) {
      return Arr::get($this->items, $key, $default);
    });

    Collection::macro('recursive', function () {
      return $this->map(function ($value) {
        if (is_array($value) || is_object($value)) {
          return collect($value)->recursive();
        }

        return $value;
      });
    });
  }

  /**
   * Initialise the Codo binary.
   *
   * @return void
   */
  protected function initialiseCodo(): void
  {
    $filepath = $this->locateCodoConfigurationFile(getcwd());

    $config = is_file($filepath) ? (new YamlConfigParser)->parse($filepath) : null;

    if (! is_null($config)) {
      config(['logging.channels.single.path' => $config->getWorkingDirectory('codo.log')]);

      $this->mapCommandPaths($config);
    }

    app()->bind('codo', function ($app) use ($filepath, $config) {
      return [
        'version' => config('app.version'),
        'file'    => $filepath,
        'config'  => $config,
      ];
    });
  }

  /**
   * Retrieve the absolute path to the codo.yml file.
   *
   * @param  string  $directory
   * @return string|null
   */
  protected function locateCodoConfigurationFile(string $directory): ?string
  {
    $expected = sprintf('%s/codo.yml', $directory);

    if (is_file($expected)) {
      return $expected;
    }

    $parent = dirname($directory);

    if (in_array($parent, ['/', '\\', '.'])) {
      return null;
    }

    return $this->locateCodoConfigurationFile($parent);
  }

  /**
   * Map additional command paths.
   *
   * @param  \Codohq\Binary\Configuration  $config
   * @return void
   */
  protected function mapCommandPaths(Configuration $config): void
  {
    $paths = $config->getCommandDirectories();

    $paths = array_unique(Arr::wrap($paths));

    $paths = array_filter($paths, function ($path) {
      return is_dir($path);
    });

    if (empty($paths)) {
      return;
    }

    foreach ((new Finder)->in($paths)->files() as $command) {
      include_once $command->getPathname();

      $command = str_replace(
        ['/', '.php'],
        ['\\', ''],
        basename($command->getPathname())
      );

      if (is_subclass_of($command, Command::class) && ! (new ReflectionClass($command))->isAbstract()) {
        Artisan::starting(function ($artisan) use ($command) {
          $artisan->resolve($command);
        });
      }
    }
  }
}
