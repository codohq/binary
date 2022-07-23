<?php

namespace Codo\Binary\Providers;

use Illuminate\Support\ServiceProvider;

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
      $config = $this->locateCodoConfigurationFile(getcwd());

      return [
        'version' => config('app.version'),
        'file'    => $config,
        'config'  => is_file($config) ? yaml_parse_file($config) : null,
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
