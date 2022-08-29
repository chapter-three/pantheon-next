<?php

namespace Drupal\pantheon_next\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Component\Serialization\Json;
use Drupal\pantheon_next\Form\PantheonNextInstallerForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a base class to build a listing entities.
 *
 * @ingroup pantheon_next
 */
class PantheonNextListBuilder extends EntityListBuilder {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected FormBuilderInterface $formBuilder;

  /**
   * Constructs a new PantheonNextListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, FormBuilderInterface $form_builder) {
    parent::__construct($entity_type, $storage);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['site'] = $this->t('Next.js Site');
    $header['base_url'] = $this->t('Base URL');
    $header['consumer'] = $this->t('Consumer');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $options = [
      'attributes' => [
        'class' => ['use-ajax'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode(['width' => 600]),
      ],
      'query' => \Drupal::destination()->getAsArray(),
    ];
    $row['site'] = $entity->getNextSite()->toLink($entity->getNextSite()
      ->label(), 'edit-form', $options);
    $row['base_url'] = $entity->getNextSite()->getBaseUrl();
    $row['consumer'] = ($consumer = $entity->getConsumer()) ? $consumer->toLink($consumer->label(), 'edit-form', $options) : $this->t('Error: Not specified');
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();

    if (count($build['table']['#rows'])) {
      return $build;
    }

    // Show the installer form if no connection has been created.
    return $this->formBuilder->getForm(PantheonNextInstallerForm::class);
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

    // We can only generate secrets if Consumer is specified.
    if ($entity->getConsumer()) {
      $operations['environment'] = [
        'title' => $this->t('Generate Secret'),
        'url' => $this->ensureDestination($entity->toUrl('environment')),
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
