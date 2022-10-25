<?php

namespace Codohq\Binary;

class PathObject
{
  /**
   * Holds the absolute path.
   *
   * @var string|null
   */
  protected ?string $absolutePath = null;

  /**
   * Instantiate a new Codo Path object.
   *
   * @param  string  $base
   * @param  string|null  $path  null
   * @return void
   */
  public function __construct(protected string $base, protected ?string $path = null)
  {
    $path = join(DIRECTORY_SEPARATOR, array_filter([$this->base, $this->path]));

    $this->absolutePath = realpath($path);
  }

  /**
   * Retrieves the path in its absolute form.
   *
   * @param  string|null  $subpath  null
   * @return string
   */
  public function asAbsolute(?string $subpath = null): string
  {
    return join(DIRECTORY_SEPARATOR, array_filter([
      $this->absolutePath, $subpath,
    ]));
  }

  /**
   * Retrieves the path in its relative form.
   *
   * @param  string|null  $subpath  null
   * @return string
   */
  public function asRelative(?string $subpath = null): string
  {
    return join(DIRECTORY_SEPARATOR, array_filter([
      $this->path, $subpath,
    ]));
  }

  /**
   * Retrieves the base path.
   *
   * @return string
   */
  public function asBasePath(): string
  {
    return $this->base;
  }
}
