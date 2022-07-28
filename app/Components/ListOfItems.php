<?php

namespace Codohq\Binary\Components;

use Closure;
use Illuminate\Contracts\Support\Arrayable;

class ListOfItems implements Arrayable
{
  /**
   * Holds the list of items.
   *
   * @var array
   */
  protected array $items = [];

  /**
   * Holds the group heading.
   *
   * @var string
   */
  protected string $heading;

  /**
   * Holds the group value.
   *
   * @var string|null
   */
  protected ?string $value;

  /**
   * Clone the object.
   *
   * @return void
   */
  public function __clone()
  {
    $this->items = [];
    $this->value = null;
  }

  /**
   * Add an item to the list.
   *
   * @param  string  $heading
   * @param  mixed  $value
   * @param  callable|null  $callback  null
   * @return $this
   */
  public function addItem(string $heading, mixed $value, ?callable $callback = null): self
  {
    $this->items[] = [
      'type'    => 'item',
      'heading' => $heading,
      'value'   => is_callable($callback) ? $callback($value) : $value,
    ];

    return $this;
  }

  /**
   * Add numerous items to the list at once.
   *
   * @param  array  $items
   * @param  callable|null  $callback  null
   * @return $this
   */
  public function addItems(array $items, ?callable $callback = null): self
  {
    foreach ($items as $heading => $value) {
      $this->addItem($heading, $value, $callback);
    }

    return $this;
  }

  /**
   * Add a group to the list.
   *
   * @param  string  $heading
   * @param  \Closure  $callback
   * @return $this
   */
  public function addGroup(string $heading, Closure $callback): self
  {
    $group = clone $this;

    $group->setHeading($heading);

    $callback($group);

    $this->items[] = $group;

    return $group;
  }

  /**
   * Set the heading of the group.
   *
   * @param  string  $heading
   * @return $this
   */
  public function setHeading(string $heading): self
  {
    $this->heading = $heading;

    return $this;
  }

  /**
   * Retrieve the heading of the group.
   *
   * @return string
   */
  public function getHeading(): string
  {
    return $this->heading;
  }

  /**
   * Set the value of the group.
   *
   * @param  mixed  $value
   * @return $this
   */
  public function setValue(mixed $value): self
  {
    $this->value = $value;

    return $this;
  }

  /**
   * Retrieve the value of the group.
   *
   * @return string|null
   */
  public function getValue(): ?string
  {
    return $this->value;
  }

  /**
   * Get the instance as an array.
   *
   * @return array<TKey, TValue>
   */
  public function toArray()
  {
    return (array) $this->items;
  }
}
