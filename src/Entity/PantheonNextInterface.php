<?php

namespace Drupal\pantheon_next\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining PantheonNext entities.
 *
 * @ingroup pantheon_next
 */
interface PantheonNextInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the PantheonNext creation timestamp.
   *
   * @return int
   *   Creation timestamp of the PantheonNext.
   */
  public function getCreatedTime();

  /**
   * Sets the PantheonNext creation timestamp.
   *
   * @param int $timestamp
   *   The PantheonNext creation timestamp.
   *
   * @return \Drupal\pantheon_next\Entity\PantheonNextInterface
   *   The called PantheonNext entity.
   */
  public function setCreatedTime($timestamp);

}
