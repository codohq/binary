<?php

namespace Codohq\Binary\Concerns;

trait InteractsWithEligibility
{
  /**
   * Holds the eligibility status.
   *
   * @var boolean
   */
  protected bool $eligibility = false;

  /**
   * Mark the object as eligible.
   *
   * @return $this
   */
  public function eligible(): static
  {
    $this->eligibility = true;

    return $this;
  }

  /**
   * Mark the object as ineligible.
   *
   * @return $this
   */
  public function ineligible(): static
  {
    $this->eligibility = false;

    return $this;
  }

  /**
   * Check if the object is eligible.
   *
   * @return boolean
   */
  public function isEligible(): bool
  {
    return $this->eligibility === true;
  }

  /**
   * Check if the object is ineligible.
   *
   * @return boolean
   */
  public function isIneligible(): bool
  {
    return ! $this->isEligible();
  }
}
