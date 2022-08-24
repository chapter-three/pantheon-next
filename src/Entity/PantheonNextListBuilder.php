<?php

namespace Drupal\pantheon_next\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;

/**
 * Defines a base class to build a listing entities.
 *
 * @ingroup pantheon_next
 */
class PantheonNextListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['site'] = $this->t('Next.js Site');
    $header['consumer'] = $this->t('Consumer');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['site'] = $entity->getNextSite()->toLink($entity->getNextSite()->label(), 'edit-form');
    $row['consumer'] = $entity->getConsumer()->toLink($entity->getConsumer()->label(), 'edit-form');
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);
    $ajax_attributes = [
      'class' => ['use-ajax'],
      'data-dialog-type' => 'modal',
      'data-dialog-options' => Json::encode(['width' => 600]),
    ];
    if ($entity->access('update')) {
      $operations['environment'] = [
        'title' => $this->t('Generate Secret'),
        'url' => $this->ensureDestination(URL::fromRoute('pantheon_next.environment', ['pantheon_next' => $entity->id()])),
        'attributes' => $ajax_attributes,
      ];
    }
    if (!empty($operations['edit'])) {
      $operations['edit']['attributes'] = $ajax_attributes;
    }
    if (!empty($operations['delete'])) {
      $operations['delete']['attributes'] = $ajax_attributes;
      $operations['delete']['weight'] = 999;
    }
    return $operations;
  }

}
