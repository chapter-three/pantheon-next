<?php

namespace Drupal\pantheon_next;

/**
 * Interface for the pantheon next installer.
 */
interface PantheonNextInstallerInterface {

  /**
   * Runs installer tasks.
   *
   * @return void
   */
  public function run();

}
