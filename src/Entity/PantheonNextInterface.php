<?php

namespace Drupal\pantheon_next\Entity;

use Drupal\consumers\Entity\Consumer;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\next\Entity\NextSiteInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining PantheonNext entities.
 *
 * @ingroup pantheon_next
 */
interface PantheonNextInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the referenced next site entity.
   *
   * @return \Drupal\next\Entity\NextSiteInterface|null
   *   The referenced next site entity.
   */
  public function getNextSite(): ?NextSiteInterface;

  /**
   * Gets the referenced OAuth consumer.
   *
   * @return \Drupal\consumers\Entity\Consumer|null
   *   Returns the OAuth consumer.
   */
  public function getConsumer(): ?Consumer;

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
