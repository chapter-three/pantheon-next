<?php

namespace Drupal\pantheon_next\Entity\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for PantheonNext delete form.
 *
 * @ingroup pantheon_next
 */
class PantheonNextDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $entity = $this->getEntity();
    return $this->t('You are about to delete %name', ['%name' => $entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $consumer = $this->getEntity()->getConsumer();
    if (!$consumer) {
      return parent::getDescription();
    }

    return $this->t('<strong>Delete %name will also delete the %consumer consumer</strong>. This action cannot be undone.', [
      '%name' => $this->getEntity()->label(),
      '%consumer' => $consumer->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Yes, Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['actions']['cancel']['#attributes']['class'][] = 'dialog-cancel';
    return $form;
  }

}
