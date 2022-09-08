<?php

namespace Drupal\Tests\pantheon_next\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\pantheon_next\PantheonNextInstallerInterface;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * Tests the pantheon_next entity.
 *
 * @coversDefaultClass \Drupal\pantheon_next\Entity\PantheonNext
 *
 * @group pantheon_next
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

    $this->pantheonNextInstaller = $this->container->get('pantheon_next.installer');
  }

  /**
   * Tests referenced next_site and consumer deletion.
   */
  public function testCascadeDelete() {
    // Running the installer will create a next_site and consumer.
    $this->pantheonNextInstaller->run();

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');
    $pantheon_next_entities = $entity_type_manager->getStorage('pantheon_next')->loadMultiple();

    $this->assertCount(1, $pantheon_next_entities);
    $this->assertCount(1, $entity_type_manager->getStorage('next_site')->loadMultiple());
    $this->assertCount(1, $entity_type_manager->getStorage('consumer')->loadMultiple());

    $pantheon_next = reset($pantheon_next_entities);
    $pantheon_next->delete();

    $this->assertCount(0, $entity_type_manager->getStorage('pantheon_next')->loadMultiple());
    $this->assertCount(0, $entity_type_manager->getStorage('next_site')->loadMultiple());
    $this->assertCount(0, $entity_type_manager->getStorage('consumer')->loadMultiple());
  }

}
