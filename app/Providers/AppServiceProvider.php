<?php

namespace Codohq\Binary\Providers;

use Phar;
use Illuminate\Support\ServiceProvider;
use Codohq\Binary\Parsers\YamlConfigParser;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    app()->bind('codo', function ($app) {
      $filepath = $this->locateCodoConfigurationFile(getcwd());

      $config = is_file($filepath) ? (new YamlConfigParser)->parse($filepath) : null;

      if (! is_null($config)) {
        config(['logging.channels.single.path' => $config->getWorkingDirectory('codo.log')]);
      }

      return [
        'version' => config('app.version'),
        'file'    => $filepath,
        'config'  => $config,
      ];
    });
  }

  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    //
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

    $parent = dirname($directory);

    if (in_array($parent, ['/', '\\', '.'])) {
      return null;
    }

    if (! is_file($expected)) {
      return $this->locateCodoConfigurationFile($parent);
    }

    return $expected;
  }
}
