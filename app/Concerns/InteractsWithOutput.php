<?php

namespace Codohq\Binary\Concerns;

use function Termwind\{ render };

trait InteractsWithOutput
{
  /**
   * Check if the current working directory is eligible or not.
   *
   * @return boolean
   */
  public function isEligible(): bool
  {
    $codo = app('codo');

    return ! empty($codo['file']) and ! empty($codo['config']);
  }

  /**
   * Check if the current working directory is ineligible or not.
   *
   * @return boolean
   */
  public function isIneligible(): bool
  {
    return ! $this->isEligible();
  }

  /**
   * Render a warning for when a command is being run outside of a Codo project.
   *
   * @return string
   */
  public function ineligible(): string
  {
    return <<<HTML
      <div class="mt-1 text-yellow">
        Unable to locate a codo.yml file.
      </div>
    HTML;
  }
}
