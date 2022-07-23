<?php

namespace Codohq\Binary\Concerns;

use function Termwind\{ render };

trait InteractsWithOutput
{
  /**
   * Render a warning for when a command is being run outside of a Codo project.
   *
   * @return void
   */
  public function ineligible(): void
  {
    $this->warn('Unable to locate codo.yml file.');
  }

  /**
   * Render an error message.
   *
   * @param  string  $string
   * @param  string|null  $verbosity  null
   * @return void
   */
  public function error($string, $verbosity = null)
  {
    render(<<<HTML
      <div class="my-1">
        <div class="flex justify-between bg-red-400 text-red-900 w-full">
          <span class="px-1">An error occurred!</span>
          <span class="px-1 bg-black font-bold text-white uppercase">Codo</span>
        </div>
        <pre class="px-1">{$string}</pre>
      </div>
    HTML);
  }

  /**
   * Render a warning message.
   *
   * @param  string  $string
   * @param  string|null  $verbosity  null
   * @return void
   */
  public function warn($string, $verbosity = null)
  {
    render(<<<HTML
      <div class="my-1">
        <div class="flex justify-between bg-amber-400 text-amber-900 w-full">
          <span class="px-1">Warning!</span>
          <span class="px-1 bg-black font-bold text-white uppercase">Codo</span>
        </div>
        <pre class="px-1">{$string}</pre>
      </div>
    HTML);
  }

  /**
   * Render an info message.
   *
   * @param  string  $string
   * @param  string|null  $verbosity  null
   * @return void
   */
  public function info($string, $verbosity = null)
  {
    render(<<<HTML
      <div class="my-1">
        <div class="flex justify-between bg-sky-400 text-sky-900 w-full">
          <span class="px-1">Info!</span>
          <span class="px-1 bg-black font-bold text-white uppercase">Codo</span>
        </div>
        <pre class="px-1">{$string}</pre>
      </div>
    HTML);
  }

  /**
   * Render a command message.
   *
   * @param  string  $string
   * @param  string|null  $verbosity  null
   * @return void
   */
  public function command($string, $verbosity = null)
  {
    render(<<<HTML
      <div class="my-1">
        <div class="flex justify-between bg-Indigo-400 text-Indigo-900 w-full">
          <span class="px-1">Command!</span>
          <span class="px-1 bg-black font-bold text-white uppercase">Codo</span>
        </div>
        <pre class="px-1">{$string}</pre>
      </div>
    HTML);
  }
}
