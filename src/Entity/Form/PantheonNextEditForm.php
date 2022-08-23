<?php

namespace Drupal\pantheon_next\Entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * PantheonNextEditForm controller.
 *
 * @ingroup pantheon_next
 */
class PantheonNextEditForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return 'entity.pantheon_next.collection';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;
    if ($entity->isNew()) {
      unset($form['consumer']);
      $form['site_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Site Name'),
        '#description' => $this->t('Example: Blog or Marketing site.'),
        '#required' => TRUE,
      ];
      $form['base_url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Next.js Base URL'),
        '#description' => $this->t('Enter the base URL for the Next.js site. Example: <em>https://example.com</em>.'),
        '#required' => TRUE,
      ];
      $form['preview_url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Preview URL'),
        '#description' => $this->t('Enter the preview URL. Example: <em>https://example.com/api/preview</em>.'),
      ];
    }

    unset($form['actions']['delete']);
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute($this->getCancelUrl()),
      '#title' => $this->t('Cancel'),
      '#attributes' => [
        'class' => ['button', 'dialog-cancel'],
      ],
      '#weight' => 5,
    ];
    $form['actions']['#weight'] = 999;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    if ($entity->isNew()) {
      $installer = \Drupal::service('pantheon_next.installer');
      $user = $installer->createUserAndRole();
      $entity->set('consumer', $installer->createClientScopes($user, $form_state->getValue('site_name')));
      $entity->set('next_site', $installer->createNextSite($form_state->getValue('site_name'), $form_state->getValue('preview_url'), $form_state->getValue('base_url')));
    }

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:

        $this->messenger()->addMessage($this->t('Successfully created %label.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Successfully updated %label.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect($this->getCancelUrl());
  }

}
