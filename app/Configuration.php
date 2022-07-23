<?php

namespace Codohq\Binary;

use Illuminate\Support\Collection;

class Configuration
{
  /**
   * Holds the configuration data.
   *
   * @var \Illuminate\Support\Collection
   */
  protected Collection $data;

  /**
   * Instantiate a new Codo configuration object.
   *
   * @param  array  $data
   * @param  string  $workdir
   * @return void
   */
  public function __construct(array $data, protected string $workdir)
  {
    $this->data = (new Collection($data))->recursive();
  }

  /**
   * Retrieve the project domain.
   *
   * @return string|null
   */
  public function getProject(): ?string
  {
    $fallback = basename($this->getWorkingDirectory());

    return $this->get('settings.name', $fallback);
  }

  /**
   * Retrieve the project domain.
   *
   * @return string|null
   */
  public function getDomain(): ?string
  {
    $fallback = basename($this->getWorkingDirectory()).'.local';

    return $this->get('settings.domain', $fallback);
  }

  /**
   * Retrieve the project environment.
   *
   * @return string
   */
  public function getEnvironment(): string
  {
    return $this->get('settings.environment', 'local');
  }

  /**
   * Retrieve the working directory.
   *
   * @param  string|null  $subpath  null
   * @return string
   */
  public function getWorkingDirectory(?string $subpath = null): string
  {
    return $this->resolvePath('', $subpath, true);
  }

  /**
   * Retrieve the docker directory.
   *
   * @param  string|null  $subpath  null
   * @param  boolean  $absolute  false
   * @return string
   */
  public function getDocker(?string $subpath = null, bool $absolute = false): string
  {
    $path = $this->get('codo.components.docker', './docker');

    return $this->resolvePath($path, $subpath, $absolute);
  }

  /**
   * Retrieve the entrypoint directory.
   *
   * @param  string|null  $subpath  null
   * @param  boolean  $absolute  false
   * @return string
   */
  public function getEntrypoint(?string $subpath = null, bool $absolute = false): string
  {
    $path = $this->get('codo.components.entrypoint', './entrypoint');

    return $this->resolvePath($path, $subpath, $absolute);
  }

  /**
   * Retrieve the framework directory.
   *
   * @param  string|null  $subpath  null
   * @param  boolean  $absolute  false
   * @return string
   */
  public function getFramework(?string $subpath = null, bool $absolute = false): string
  {
    $path = $this->get('codo.components.framework', './framework');

    return $this->resolvePath($path, $subpath, $absolute);
  }

  /**
   * Retrieve the theme directory.
   *
   * @param  string|null  $subpath  null
   * @param  boolean  $absolute  false
   * @return string
   */
  public function getTheme(?string $subpath = null, bool $absolute = false): string
  {
    $path = $this->get('codo.components.theme', './theme');

    return $this->resolvePath($path, $subpath, $absolute);
  }

  /**
   * Retrieve the command directories.
   *
   * @param  boolean  $absolute  true
   * @return array
   */
  public function getCommandDirectories(bool $absolute = true): array
  {
    $paths = $this->get('codo.commands', new Collection)
      ->map(function ($path) use ($absolute) {
        return ($absolute and str_starts_with($path, '.'))
          ? $this->getWorkingDirectory($path, true)
          : $path;
      });

    return $paths->toArray();
  }

  /**
   * Retrieve a specific item from the configuration.
   *
   * @param  string  $path
   * @param  mixed  $fallback  null
   * @return mixed
   */
  public function get(string $path, mixed $fallback = null): mixed
  {
    return $this->data->path($path, $fallback);
  }

  /**
   * Resolve the given path.
   *
   * @param  string  $path
   * @param  string|null  $subpath  null
   * @param  boolean  $absolute  false
   * @return string
   */
  protected function resolvePath(string $path, ?string $subpath = null, bool $absolute = false): string
  {
    $fullpath = implode($separator = DIRECTORY_SEPARATOR, [
      rtrim($path, $separator),
      trim($subpath, $separator),
    ]);

    if (! $absolute) {
      return rtrim($fullpath, $separator);
    }

    $fullpath = implode($separator, [
      rtrim($this->workdir, $separator),
      trim($fullpath, $separator),
    ]);

    return rtrim($fullpath, $separator);
  }
}
