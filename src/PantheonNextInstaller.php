<?php

namespace Drupal\pantheon_next;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Password\DefaultPasswordGenerator;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\simple_oauth\Service\KeyGeneratorService;

/**
 * Next.js installer.
 *
 * @ingroup pantheon_next
 */
class PantheonNextInstaller implements PantheonNextInstallerInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $defaultPasswordGenerator;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $keyGeneratorService;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs the NextInstaller service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Gets config data.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Used to obtain data from various entity types.
   * @param \Drupal\Core\Password\DefaultPasswordGenerator $password_generator
   *   Calls the core password generator in order to create secret keys.
   * @param \Drupal\simple_oauth\Service\KeyGeneratorService $key_generator_service
   *   Allows us to programmatically generate public and private oauth keys.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, DefaultPasswordGenerator $password_generator, KeyGeneratorService $key_generator_service, FileSystemInterface $file_system) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->defaultPasswordGenerator = $password_generator;
    $this->keyGeneratorService = $key_generator_service;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public function run() {
    $this->createPathPatterns();
    $user = $this->createUserAndRole();
    $this->createOauthKeys();
    $this->createSiteAndConsumer($user, 'Next.js Site', 'http://localhost:3000/api/preview/', 'http://localhost:3000');
    $this->setDefaultTheme();
  }

  /**
   * Configure default content type path aliases.
   */
  public function createPathPatterns() {
    $patterns = [
      'article' => [
        'label' => 'Article',
        'pattern' => '/articles/[node:title]',
      ],
      'recipes' => [
        'label' => 'Recipe',
        'pattern' => '/recipes/[node:title]',
      ],
      'page' => [
        'label' => 'Page',
        'pattern' => '/[node:title]',
      ],
    ];

    $storage = $this->entityTypeManager->getStorage('pathauto_pattern');
    foreach ($patterns as $bundle => $params) {
      // Checking if patterns exists
      $exists = $storage->getQuery()
        ->condition('selection_criteria.*.id', 'entity_bundle:node')
        ->condition('selection_criteria.*.bundles.' . $bundle, $bundle)
        ->accessCheck(FALSE)
        ->execute();
      if (empty($exists)) {
        $uuid = \Drupal::service('uuid')->generate();
        $pattern = $storage->create([
          'id' => $bundle,
          'label' => $params['label'],
          'type' => 'canonical_entities:node',
          'pattern' => $params['pattern'],
          'selection_criteria' => [
            $uuid => [
              'uuid' => $uuid,
              'id' => 'entity_bundle:node',
              'negate' => FALSE,
              'context_mapping' => [
                'node' => 'node',
              ],
              'bundles' => [
                $bundle => $bundle,
              ],
            ],
          ],
        ]);
        $pattern->save();
      }
    }
  }

  /**
   * Configure default content type path aliases.
   */
  public function createNextSite($label = 'Next.js Site', $preview_url = '/api/preview/', $base_url = '') {
    $site_id = $this->getMachineName($label);
    $site_storage = $this->entityTypeManager->getStorage('next_site');
    if (!$next_site = $site_storage->load($site_id)) {
      $next_site = $site_storage->create([
        'id' => $site_id,
        'label' => $label,
        'base_url' => $base_url,
        'preview_url' => $preview_url,
        'preview_secret' => $this->defaultPasswordGenerator->generate(21),
      ]);
      $next_site->save();
    }

    $this->setSiteResolver();
    return $next_site;
  }

  /**
   * Configure default content type path aliases.
   */
  public function createUserAndRole() {
    $role_id = 'next_site';
    $role_storage = $this->entityTypeManager->getStorage('user_role');
    if (!$role = $role_storage->load($role_id)) {
      /** @var \Drupal\user\RoleInterface $role */
      $role = $role_storage->create([
        'id' => $role_id,
        'label' => 'Next.js Site',
      ]);

      $role->grantPermission('bypass node access');
      $role->grantPermission('issue subrequests');
      $role->grantPermission('access user profiles');

      $role->save();
    }

    // TODO: Make this configurable.
    $email = 'no-reply@example.com';
    $user_storage = $this->entityTypeManager->getStorage('user');

    $users = $user_storage->loadByProperties(['mail' => $email]);
    if ($user = reset($users)) {
      return $user;
    }
    else {
      $user = $user_storage->create([]);
      $user->setPassword($this->defaultPasswordGenerator->generate(21));
      $user->setEmail($email);
      $user->setUsername('nextjs');
      $user->set('langcode', 'en');
      $user->set('init', $email);
      $user->set('preferred_langcode', 'en');
      $user->addRole($role_id);
      $user->enforceIsNew();
      $user->activate();
      $user->save();
      return $user;
    }
  }

  /**
   * Generate SimpleOauth keys.
   */
  public function createOauthKeys() {
    $oauth = $this->configFactory->getEditable('simple_oauth.settings');
    if (empty($oauth->get('public_key')) && empty($oauth->get('private_key'))) {
      $path = 'public://oauth-keys';
      // Check if the private file stream wrapper is ready to use.
      if (\Drupal::service('stream_wrapper_manager')
        ->isValidScheme('private')) {
        $path = 'private://oauth-keys';
      }
      $this->fileSystem->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY);
      $realpath = $this->fileSystem->realpath($path);

      $this->keyGeneratorService->generateKeys($realpath);
      $oauth->set('public_key', "$realpath/public.key")
        ->set('private_key', "$realpath/private.key")
        ->save();
    }
  }

  /**
   * Create a consumer and set scopes.
   */
  public function createClientScopes($user, $label = 'Pantheon Next.js') {
    $consumer_storage = $this->entityTypeManager->getStorage('consumer');
    $consumer_entities = $consumer_storage->loadByProperties(['label' => $label]);
    $all_consumer_entities = $consumer_storage->loadMultiple();
    if (!$consumer = reset($consumer_entities)) {
      $consumer = $consumer_storage->create([]);
      $consumer->set('label', $label . ' Consumer');
      $consumer->set('secret', $this->defaultPasswordGenerator->generate(21));
      $consumer->set('is_default', empty($all_consumer_entities) ?? TRUE);
      $consumer->set('redirect', '');
      $consumer->set('roles', 'next_site');
      $consumer->set('user_id', $user->id());
      $consumer->save();
    }
    return $consumer;
  }

  /**
   * Create new Pantheon Next.js site entity.
   */
  public function createSiteAndConsumer($user, $label = 'Pantheon Next.js Site', $preview_url = 'http://localhost:3000/api/preview/', $base_url = 'http://localhost:3000') {
    $label = preg_replace("#[[:punct:]]#", "", $label);
    $consumer = $this->createClientScopes($user, $label);
    $next_site = $this->createNextSite($label, $preview_url, $base_url);
    $this->setSiteResolver();
    $pantheon_next = $this->entityTypeManager->getStorage('pantheon_next')
      ->create([
        'next_site' => $next_site->id(),
        'consumer' => $consumer->id(),
      ]);
    $pantheon_next->save();
    return $pantheon_next;
  }

  /**
   * Create NextEntityTypeConfig entities for content types.
   */
  protected function setSiteResolver() {
    $sites = [];
    $site_storage = $this->entityTypeManager->getStorage('next_site');
    $site_entities = $site_storage->loadMultiple();
    foreach ($site_entities as $site) {
      $sites[$site->id()] = $site->id();
    }

    if ($types = $this->entityTypeManager->getStorage('node_type')
      ->loadMultiple()) {
      foreach ($types as $type) {
        $next_entity_type = $this->entityTypeManager->getStorage('next_entity_type_config');
        if ($existing = $next_entity_type->load("node.{$type->id()}")) {
          $existing->setConfiguration(['sites' => $sites]);
          $existing->save();
        }
        else {
          $next_entity_type->create([
            'id' => "node.{$type->id()}",
            'site_resolver' => 'site_selector',
            'configuration' => [
              'sites' => $sites,
            ],
          ])->save();
        }
      }
    }
  }

  protected function setDefaultTheme() {
    $this->configFactory->getEditable('system.theme')
      ->set('default', 'claro')
      ->save();
  }

  /**
   * Generates a machine name from a string.
   */
  protected function getMachineName($string) {
    $transliterated = \Drupal::transliteration()
      ->transliterate($string, LanguageInterface::LANGCODE_DEFAULT, '_');
    $transliterated = mb_strtolower($transliterated);
    $transliterated = preg_replace('@[^a-z0-9_.]+@', '_', $transliterated);
    return $transliterated;
  }

}
