<?php

namespace Drupal\next_for_drupal_pantheon\Commands;

use Drupal\Core\Url;
use Drush\Commands\DrushCommands;
use Drupal\next_for_drupal_pantheon\PantheonNextInstaller;

/**
 * PantheonNextCommands drush commands.
 */
class PantheonNextCommands extends DrushCommands {

  /**
   * Pantheon Next installer service.
   *
   * @var \Drupal\next_for_drupal_pantheon\PantheonNextInstaller
   */
  protected $pantheonNextInstaller;

  /**
   * PantheonNextCommands constructor.
   *
   * @param \Drupal\next_for_drupal_pantheon\PantheonNextInstaller $installer
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
    $next_for_drupal_pantheon = $this->pantheonNextInstaller->createSiteAndConsumer($user, $label, $options['preview_url'], $options['base_url']);
    if ($next_for_drupal_pantheon) {
      $this->output()
        ->writeln($label . ' sucessfully created. Go to ' . URL::fromRoute('entity.next_for_drupal_pantheon.collection')
          ->setAbsolute()
          ->toString() . ' to genereate secret keys.');
    }
  }

}
