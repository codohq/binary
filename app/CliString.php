<?php

namespace Codohq\Binary;

use ArrayAccess;
use ArrayIterator;
use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\Console\Input\StringInput;

class CliString implements ArrayAccess, Arrayable
{
  /**
   * Holds the command.
   *
   * @var string
   */
  protected string $command;

  /**
   * Holds the arguments.
   *
   * @var array
   */
  protected array $arguments = [];

  /**
   * Instantiate a new CLI string object.
   * 
   * @param  string  $command
   * @return void
   */
  public function __construct(string $command)
  {
    $this->command = $this->normalize($command);

    $input = new StringInput($this->command);

    $this->arguments = invade($input)->tokenize($this->command);
  }

  /**
   * Normalize the given value.
   *
   * @param  string  $value
   * @return string
   */
  protected function normalize(string $value): string
  {
    $value = trim($value);

    // Remove leading whitespace on each new line
    $value = preg_replace('~^\h+|\h+$|(\R){2,}|(\s){2,}~m', '$1$2', $value);

    return $value;
  }

  /**
   * Return the command string.
   *
   * @return string
   */
  public function __toString(): string
  {
    return $this->command;
  }

  /**
   * Get an iterator for the items.
   *
   * @return \ArrayIterator
   */
  public function getIterator()
  {
    return new ArrayIterator($this->arguments);
  }

  /**
   * Determine if an item exists at an offset.
   *
   * @param  mixed  $key
   * @return bool
   */
  public function offsetExists($key)
  {
    return array_key_exists($key, $this->arguments);
  }

  /**
   * Get an item at a given offset.
   *
   * @param  mixed  $key
   * @return mixed
   */
  public function offsetGet($key)
  {
    return $this->arguments[$key];
  }

  /**
   * Set the item at a given offset.
   *
   * @param  mixed  $key
   * @param  mixed  $value
   * @return void
   */
  public function offsetSet($key, $value)
  {
    if (is_null($key)) {
      $this->arguments[] = $value;
    } else {
      $this->arguments[$key] = $value;
    }
  }

  /**
   * Unset the item at a given offset.
   *
   * @param  string  $key
   * @return void
   */
  public function offsetUnset($key)
  {
    unset($this->arguments[$key]);
  }

  /**
   * Get the instance as an array.
   *
   * @return array<TKey, TValue>
   */
  public function toArray()
  {
    return $this->arguments;
  }
}
