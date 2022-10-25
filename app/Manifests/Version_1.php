<?php

namespace Codohq\Binary\Manifests;

use Codohq\Binary\PathObject;
use Codohq\Binary\Contracts\Manifest;
use Illuminate\Support\{ Collection, Str };
use Illuminate\Contracts\Support\Arrayable;
use Codohq\Binary\Exceptions\InvalidDockerComposePath;
use RomaricDrigon\MetaYaml\{ MetaYaml, Loader\YamlLoader };

class Version_1 implements Manifest
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
   * Validate the given data and instantiate a new manifest object.
   *
   * @param  array  $data
   * @param  string  $workdir
   * @return $this
   */
  public static function validate(array $data, string $workdir): self
  {
    $schema = (new YamlLoader)->loadFromFile(__DIR__.'/Version_1.yml');

    (new MetaYaml($schema))->validate($data);

    return new static($data, $workdir);
  }

  /**
   * Retrieve the manifest version.
   *
   * @return float
   */
  public function manifestVersion(): float
  {
    return (float) $this->data->get('version');
  }

  /**
   * Retrieves the name of the Codo project.
   *
   * @return string
   */
  public function name(): string
  {
    $fallback = basename($this->root()->asAbsolute());

    return Str::slug($this->data->path('codo.name', $fallback));
  }

  /**
   * Retrieves the environment of the Codo project.
   *
   * @return string
   */
  public function environment(): string
  {
    return (string) $this->data->path('codo.environment');
  }

  /**
   * Retrieves all of the default Codo-generated environment variables.
   *
   * @return array
   */
  protected function defaultEnvironmentVariables(): array
  {
    return [
      'CODO_UID'              => trim(shell_exec('id -u')),
      'CODO_GID'              => trim(shell_exec('id -g')),
      'CODO_PROJECT'          => $this->name(),
      'CODO_DOMAIN'           => $this->get('network.hostname'),
      'CODO_BASEPATH'         => $this->root()->asAbsolute(),
      'CODO_DOCKER'           => $this->dockerPath()->asRelative(),
      'CODO_DOCKER_FULL'      => $this->dockerPath()->asAbsolute(),
      'CODO_ENTRYPOINT'       => $this->entrypoint()->asRelative(),
      'CODO_ENTRYPOINT_FULL'  => $this->entrypoint()->asAbsolute(),
      'CODO_DOCKER_NETWORK'   => sprintf('codo-network-%s', $this->name()),
    ];
  }

  /**
   * Retrieves all of the project specific environment variables.
   *
   * @return array
   */
  public function environmentVariables(): array
  {
    $variables = $this->get('environment', new Collection)
      ->mapWithKeys(function ($value, $name) {
        if (str_starts_with(strtoupper($name), 'CODO_CERTIFICATE_')) {
          if (is_array($value)) {
            $value = join(' ', $value);
          } else if ($value instanceof Arrayable) {
            $value = $value->join(' ');
          }
        }

        return [$name => $value];
      })
      ->toArray();

    return array_merge(
      $this->defaultEnvironmentVariables(), $variables ?: []
    );
  }

  /**
   * Retrieves the path to the Codo project.
   *
   * @return \Codohq\Binary\PathObject
   */
  public function root(): PathObject
  {
    return new PathObject($this->workdir);
  }

  /**
   * Retrieves the path to the Docker configuration for the Codo project.
   *
   * @return \Codohq\Binary\PathObject
   */
  public function dockerPath(): PathObject
  {
    $path = new PathObject(
      $this->workdir, $this->data->path('codo.docker')
    );

    if ($path->asAbsolute() === false) {
      throw new InvalidDockerComposePath("Unable to find any Docker Compose directory at '{$path}'.");
    }

    $composeFile = $path->asAbsolute('docker-compose.yml');

    if (! file_exists($composeFile)) {
      throw new InvalidDockerComposePath("Unable to find any Docker Compose configuration at '{$composeFile}'.");
    }

    return $path;
  }

  /**
   * Retrieves the entrypoint path of the Codo project.
   *
   * @return \Codohq\Binary\PathObject
   */
  public function entrypoint(): PathObject
  {
    return new PathObject($this->workdir, $this->get('entrypoint'));
  }

  /**
   * Retrieves a value by the given path.
   *
   * @param  string  $path
   * @param  mixed  $fallback  null
   * @return mixed
   */
  public function get(string $path, mixed $fallback = null): mixed
  {
    $settings = $this->data->get('environments')->first(fn ($x) => $x['name'] === $this->environment());

    if ($settings->isEmpty()) {
      return null;
    }

    return $settings->path($path, $fallback);
  }
}
