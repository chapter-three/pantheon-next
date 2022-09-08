<?php

namespace Drupal\Tests\pantheon_next\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\pantheon_next\PantheonNextInstallerInterface;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\user\Entity\Role;

/**
 * Tests the pantheon_next.installer service.
 *
 * @coversDefaultClass \Drupal\pantheon_next\PantheonNextInstaller
 *
 * @group pantheon_next
 */
class PantheonNextInstallerTest extends KernelTestBase {

  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'filter',
    'next',
    'image',
    'field',
    'file',
    'consumers',
    'pantheon_next',
    'node',
    'simple_oauth',
    'serialization',
    'subrequests',
    'system',
    'text',
    'token',
    'pathauto',
    'path_alias',
    'user',
  ];

  /**
   * The pantheon next installer.
   *
   * @var \Drupal\pantheon_next\PantheonNextInstallerInterface
   */
  protected PantheonNextInstallerInterface $pantheonNextInstaller;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('consumer');
    $this->installEntitySchema('pantheon_next');
    $this->installEntitySchema('pathauto_pattern');
    $this->installConfig(['field', 'file', 'filter', 'image', 'node', 'system']);
    $this->installSchema('system', ['sequences']);
    $this->installSchema('node', ['node_access']);

    $this->createContentType(['type' => 'article']);
    $this->createContentType(['type' => 'page']);
    $this->createContentType(['type' => 'other']);

    $this->pantheonNextInstaller = $this->container->get('pantheon_next.installer');
  }

  /**
   * @covers ::run
   */
  public function testRun() {
    $this->pantheonNextInstaller->run();

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');

    // Check path patterns.
    $patterns = $entity_type_manager->getStorage('pathauto_pattern')->loadMultiple();
    $this->assertContains('article', array_keys($patterns));
    $this->assertContains('page', array_keys($patterns));

    // Check user and roles.
    /** @var \Drupal\user\UserInterface $user */
    $users = $entity_type_manager->getStorage('user')->loadByProperties(['name' => 'Next.js']);
    $user = reset($users);
    $this->assertNotNull($user);
    $this->assertContains('next_js', $user->getRoles());

    $role = Role::load('next_js');
    $this->assertContains('bypass node access', $role->getPermissions());
    $this->assertContains('issue subrequests', $role->getPermissions());
    $this->assertContains('access user profiles', $role->getPermissions());

    // Check if oauth keys have been set.
    $oauth = $this->container->get('config.factory')->getEditable('simple_oauth.settings');
    $this->assertStringContainsString('/oauth-keys/public.key', $oauth->get('public_key'));

    // Check site and consumer.
    $next_site = $entity_type_manager->getStorage('next_site')->load('nextjs_site');
    $consumers = $entity_type_manager->getStorage('consumer')->loadMultiple();
    $this->assertNotNull($next_site);
    $this->assertCount(1, $consumers);

    // Check pantheon_next entity.
    /** @var \Drupal\pantheon_next\Entity\PantheonNextInterface $pantheon_next */
    $pantheon_next = $entity_type_manager->getStorage('pantheon_next')->load(1);
    $this->assertSame($pantheon_next->getNextSite()->id(), $next_site->id());
    $consumer = reset($consumers);
    $this->assertSame($pantheon_next->getConsumer()->id(), $consumer->id());

    // Ensure only 1 pantheon_next entity is created.
    $this->assertCount(1, $entity_type_manager->getStorage('pantheon_next')->loadMultiple());
  }

  /**
   * @covers ::createPathPatterns
   */
  public function testCreatePathPatterns() {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');
    $storage = $entity_type_manager->getStorage('pathauto_pattern');
    $uuid = $this->container->get('uuid')->generate();

    $storage->create([
      'id' => 'article',
      'label' => 'Article',
      'type' => 'canonical_entities:node',
      'pattern' => '/existing/pattern/for/article/[node:title]',
      'selection_criteria' => [
        $uuid => [
          'uuid' => $uuid,
          'id' => 'entity_bundle:node',
          'negate' => FALSE,
          'context_mapping' => [
            'node' => 'node',
          ],
          'bundles' => [
            'article' => 'article',
          ],
        ],
      ],
    ])->save();

    $this->pantheonNextInstaller->run();

    $patterns = $storage->loadMultiple();

    // Only page and article patterns should've been set.
    $this->assertContains('article', array_keys($patterns));
    $this->assertContains('page', array_keys($patterns));
    $this->assertNotContains('recipe', array_keys($patterns));
    $this->assertNotContains('other', array_keys($patterns));

    // Ensure existing patterns are not overriden.
    /** @var \Drupal\pathauto\PathautoPatternInterface $article_pattern */
    $article_pattern = $patterns['article'];
    $this->assertSame('/existing/pattern/for/article/[node:title]', $article_pattern->getPattern());
  }

  /**
   * @covers ::createUserAndRole
   */
  public function testCreateUserAndRole() {
    $this->pantheonNextInstaller->run();
    $this->pantheonNextInstaller->run();

    // Running twice should only create 1 user and 1 role.
    $entity_type_manager = $this->container->get('entity_type.manager');
    $this->assertCount(1, $entity_type_manager->getStorage('user')->loadMultiple());
    $this->assertCount(1, $entity_type_manager->getStorage('user_role')->loadMultiple());
  }

}
