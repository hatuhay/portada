<?php

namespace Drupal\portada\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Taxonomia portada entities.
 */
interface TaxonomiaPortadaInterface extends ConfigEntityInterface {

  /**
   * Returns the status.
   *
   * @return int
   *   Is active.
   */
  public function IsActive();

  /**
   * Returns the ad middle.
   *
   * @return string
   *   The ad in the middle.
   */
  public function getAdMiddle();

  /**
   * Returns the ad end.
   *
   * @return string
   *   The ad in the end.
   */
  public function getAdEnd();

  /**
   * Returns the ad end responsive.
   *
   * @return string
   *   The ad in the end responsive.
   */
  public function getAdEndResponsive();

  /**
   * Returns the weight.
   *
   * @return int
   *   The weight of this role.
   */
  public function getWeight();

  /**
   * Sets the weight to the given value.
   *
   * @param int $weight
   *   The desired weight.
   *
   * @return $this
   */
  public function setWeight($weight);

}
