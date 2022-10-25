<?php

namespace Codohq\Binary\Contracts;

use Codohq\Binary\PathObject;

interface Manifest
{
  /**
   * Instantiate a new Codo configuration object.
   *
   * @param  array  $data
   * @param  string  $workdir
   * @return void
   */
  public function __construct(array $data, string $workdir);

  /**
   * Validate the given data and instantiate a new manifest object.
   *
   * @param  array  $data
   * @param  string  $workdir
   * @return $this
   */
  public static function validate(array $data, string $workdir): self;

  /**
   * Retrieve the manifest version.
   *
   * @return float
   */
  public function manifestVersion(): float;

  /**
   * Retrieves the name of the Codo project.
   *
   * @return string
   */
  public function name(): string;

  /**
   * Retrieves the environment of the Codo project.
   *
   * @return string
   */
  public function environment(): string;

  /**
   * Retrieves all of the Codo-generated environment variables.
   *
   * @return array
   */
  public function environmentVariables(): array;

  /**
   * Retrieves the path to the Codo project.
   *
   * @return \Codohq\Binary\PathObject
   */
  public function root(): PathObject;

  /**
   * Retrieves the path to the Docker configuration for the Codo project.
   *
   * @return \Codohq\Binary\PathObject
   */
  public function dockerPath(): PathObject;

  /**
   * Retrieves the entrypoint path of the Codo project.
   *
   * @return \Codohq\Binary\PathObject
   */
  public function entrypoint(): PathObject;

  /**
   * Retrieves a value by the given path.
   *
   * @param  string  $path
   * @param  mixed  $fallback  null
   * @return mixed
   */
  public function get(string $path, mixed $fallback = null): mixed;
}
