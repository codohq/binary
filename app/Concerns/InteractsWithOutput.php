<?php

namespace Codo\Binary\Concerns;

use function Termwind\{ render };

trait InteractsWithOutput
{
  /**
   *
   */
  public function error($string, $verbosity = null)
  {
    render(<<<HTML
      <div class="my-1">
        <div class="flex justify-between bg-red-400 text-red-900 w-full">
          <span class="px-1">An error occurred!</span>
          <span class="px-1 bg-gray-800 text-gray-100">Codo</span>
        </div>
        <pre class="px-1">{$string}</pre>
      </div>
    HTML);
  }

  /**
   *
   */
  public function processOutput($string)
  {
    render(<<<HTML
      <div>
        <pre class="w-full px-1">{$string}</pre>
      </div>
    HTML);
  }
}
