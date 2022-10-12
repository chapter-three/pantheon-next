<?php

namespace Drupal\Tests\next_for_drupal_pantheon\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\next_for_drupal_pantheon\PantheonNextInstallerInterface;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * Tests the next_for_drupal_pantheon entity.
 *
 * @coversDefaultClass \Drupal\next_for_drupal_pantheon\Entity\PantheonNext
 *
 * @group next_for_drupal_pantheon
 */
class PantheonNextTest extends KernelTestBase {

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
    'next_for_drupal_pantheon',
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
   * @var \Drupal\next_for_drupal_pantheon\PantheonNextInstallerInterface
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
    $this->installEntitySchema('next_for_drupal_pantheon');
    $this->installEntitySchema('pathauto_pattern');
    $this->installConfig([
      'field',
      'file',
      'filter',
      'image',
      'node',
      'system'
    ]);
    $this->installSchema('system', ['sequences']);
    $this->installSchema('node', ['node_access']);

    $this->pantheonNextInstaller = $this->container->get('next_for_drupal_pantheon.installer');
  }

  /**
   * Tests referenced next_site and consumer deletion.
   */
  public function testCascadeDelete() {
    // Running the installer will create a next_site and consumer.
    $this->pantheonNextInstaller->run();

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');
    $next_for_drupal_pantheon_entities = $entity_type_manager->getStorage('next_for_drupal_pantheon')->loadMultiple();

    $this->assertCount(1, $next_for_drupal_pantheon_entities);
    $this->assertCount(1, $entity_type_manager->getStorage('next_site')->loadMultiple());
    $this->assertCount(1, $entity_type_manager->getStorage('consumer')->loadMultiple());

    $next_for_drupal_pantheon = reset($next_for_drupal_pantheon_entities);
    $next_for_drupal_pantheon->delete();

    $this->assertCount(0, $entity_type_manager->getStorage('next_for_drupal_pantheon')->loadMultiple());
    $this->assertCount(0, $entity_type_manager->getStorage('next_site')->loadMultiple());
    $this->assertCount(0, $entity_type_manager->getStorage('consumer')->loadMultiple());
  }

}
