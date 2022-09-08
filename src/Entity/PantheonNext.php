<?php

namespace Drupal\pantheon_next\Entity;

use Drupal\consumers\Entity\Consumer;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\next\Entity\NextSiteInterface;
use Drupal\user\UserInterface;

/**
 * Defines the PantheonNext entity.
 *
 * @ingroup pantheon_next
 *
 * @ContentEntityType(
 *   id = "pantheon_next",
 *   label = @Translation("Pantheon Next.js Site"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\pantheon_next\Entity\PantheonNextListBuilder",
 *     "access" = "Drupal\pantheon_next\Entity\PantheonNextAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\pantheon_next\Routing\PantheonNextHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\pantheon_next\Entity\PantheonNextAccessControlHandler",
 *     "form" = {
 *       "delete" = "Drupal\pantheon_next\Entity\Form\PantheonNextDeleteForm",
 *       "edit" = "Drupal\pantheon_next\Entity\Form\PantheonNextEditForm",
 *       "add" = "Drupal\pantheon_next\Entity\Form\PantheonNextEditForm",
 *       "environment" = "Drupal\pantheon_next\Entity\Form\PantheonNextEnvForm",
 *     },
 *   },
 *   base_table = "pantheon_next",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "collection" = "/admin/config/services/next/pantheon",
 *     "canonical" = "/admin/config/services/next/pantheon/{pantheon_next}",
 *     "delete-form" = "/admin/config/services/next/pantheon/{pantheon_next}/delete",
 *     "edit-form" = "/admin/config/services/next/pantheon/{pantheon_next}/edit",
 *     "add-form" = "/admin/config/services/next/pantheon/new",
 *     "environment" = "/admin/config/services/next/pantheon/{pantheon_next}/environment",
 *   }
 * )
 */
class PantheonNext extends ContentEntityBase implements PantheonNextInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->getNextSite()->label();
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * Get Next.js site referenced entity.
   */
  public function getNextSite(): ?NextSiteInterface {
    if (!$this->get('next_site')->isEmpty()) {
      if ($entity = $this->get('next_site')->first()->get('entity')->getTarget()) {
        return $entity->getValue();
      }
    }

    return NULL;
  }

  /**
   * Get Consumer referenced entity.
   */
  public function getConsumer(): ?Consumer {
    if (!$this->get('consumer')->isEmpty()) {
      if ($entity = $this->get('consumer')->first()->get('entity')->getTarget()) {
        return $entity->getValue();
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    parent::preCreate($storage, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the SimpleAds entity.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['next_site'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Next.js Site'))
      ->setSetting('target_type', 'next_site')
      ->setSetting('handler', 'default:next_site')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['consumer'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Consumer'))
      ->setSetting('target_type', 'consumer')
      ->setSetting('handler', 'default:consumer')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
      ])
      ->setDisplayOptions('form', [
        'label' => 'above',
        'type' => 'options_select',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'));

    return $fields;
  }

}
