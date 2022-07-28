<?php

namespace Codohq\Binary\Components\Renderers;

use Codohq\Binary\Components\GroupedList;

class GroupedListRenderer
{
  /**
   * Instantiate a new component renderer object.
   *
   * @param  \Codohq\Binary\Components\GroupedList  $items
   * @return void
   */
  public function __construct(protected GroupedList $items)
  {
    //
  }

  /**
   * Render the component.
   *
   * @return string
   */
  public function __toString(): string
  {
    return $this->render($this->items);
  }

  /**
   * Render a list of items.
   *
   * @param  \Codohq\Binary\Components\GroupedList  $items
   * @param  integer  $depth
   * @param  string  $color  blue
   * @return string
   */
  protected function render(GroupedList $items, int $depth = 0): string
  {
    $content = '';

    foreach ($items->toArray() as $item) {
      $padding = (int) $depth * 2;

      $classes = $depth !== 0 ? "pl-{$padding}" : '';

      $prefix = $depth > 0 ? '<span class="text-gray">↳</span> ' : '';

      if ($item instanceof GroupedList) {
        $content .= $this->renderGroup($item, $classes, $prefix, $depth);
        continue;
      }

      $content .= $this->renderLine($item, $classes, $prefix);
    }

    return <<<HTML
      <div>{$content}</div>
    HTML;
  }

  /**
   * Render the project name.
   *
   * @param  \Codohq\Binary\Components\GroupedList  $group
   * @param  string  $classes
   * @param  string  $prefix
   * @param  integer  $depth
   * @return string
   */
  public function renderGroup(GroupedList $group, string $classes, string $prefix, int $depth): string
  {
    $content = '';

    $value = $group->getValue() ? <<<HTML
      <span class="flex-1 content-repeat-[.] text-gray"></span>
      <span class="font-bold text-blue">{$group->getValue()}</span>
    HTML : '';

    $content .= <<<HTML
      <div class="flex space-x-1 {$classes}">
        <span class="font-bold">{$prefix}{$group->getHeading()}</span>
        {$value}
      </div>
    HTML;

    $content .= $this->render($group, $depth + 1);

    return $content;
  }

  /**
   * Render an item wrapper.
   *
   * @param  array  $item
   * @param  string  $classes
   * @param  string  $prefix
   * @return string
   */
  public function renderLine(array $item, string $classes, string $prefix): string
  {
    return <<<HTML
      <div class="flex space-x-1 {$classes}">
        <span class="font-bold">{$prefix}{$item['heading']}</span>
        <span class="flex-1 content-repeat-[.] text-gray"></span>
        {$item['value']}
      </div>
    HTML;
  }

  /**
   * Render the project name.
   *
   * @param  mixed  $value
   * @return string
   */
  public function renderProject(mixed $value): string
  {
    return <<<HTML
      <span class="bg-yellow px-1 font-bold">{$value}</span>
    HTML;
  }

  /**
   * Render an item value.
   *
   * @param  mixed  $value
   * @return string
   */
  public function renderValue(mixed $value): string
  {
    return <<<HTML
      <span class="text-blue font-bold">{$value}</span>
    HTML;
  }

  /**
   * Render the version constraints.
   *
   * @param  mixed  $value
   * @return string
   */
  public function renderVersion(mixed $value): string
  {
    $success = <<<HTML
      <span class="text-green font-bold">v{$value}</span>
    HTML;

    $failure = <<<HTML
      <span class="bg-red px-1 font-bold text-white">N/A</span>
    HTML;

    return ! empty($value) ? $success : $failure;
  }
}
