<?php

namespace Drupal\pantheon_next\Commands;

use Drupal\Core\Url;
use Drush\Commands\DrushCommands;
use Drupal\pantheon_next\PantheonNextInstaller;

/**
 * PantheonNextCommands drush commands.
 */
class PantheonNextCommands extends DrushCommands {

  /**
   * Pantheon Next installer service.
   *
   * @var \Drupal\pantheon_next\PantheonNextInstaller
   */
  protected $pantheonNextInstaller;

  /**
   * PantheonNextCommands constructor.
   *
   * @param \Drupal\pantheon_next\PantheonNextInstaller $installer
   *   Pantheon Next installer service.
   */
  public function __construct(PantheonNextInstaller $installer) {
    parent::__construct();
    $this->pantheonNextInstaller = $installer;
  }

  /**
   * Drush command to create Pantheon Next.js site.
   *
   * @param string $label
   *   Example: Blog or Marketing site.
   * @param string[] $options
   *   An array of options for the next.js site.
   *
   * @command pantheon-next:new
   * @aliases pantheon-next
   * @option preview_url
   *   Provide the preview URL. Example: https://example.com/api/preview.
   * @option base_url
   *   Enter the base URL for the Next.js site. Example: https://example.com.
   * @usage pantheon-next:new "Pantheon Next.js Site"
   *   --preview_url="https://example.com/api/preview"
   *   --base_url="https://example.com".
   */
  public function newSite($label = 'Pantheon Next.js Site', $options = [
    'preview_url' => 'https://example.com/api/preview',
    'base_url' => 'https://example.com',
  ]) {
    $user = $this->pantheonNextInstaller->createUserAndRole();
    $pantheon_next = $this->pantheonNextInstaller->createSiteAndConsumer($user, $label, $options['preview_url'], $options['base_url']);
    if ($pantheon_next) {
      $this->output()
        ->writeln($label . ' sucessfully created. Go to ' . URL::fromRoute('entity.pantheon_next.collection')
          ->setAbsolute()
          ->toString() . ' to genereate secret keys.');
    }
  }

}
