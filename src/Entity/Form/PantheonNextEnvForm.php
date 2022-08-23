<?php

namespace Drupal\pantheon_next\Entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * PantheonNextEnvForm controller.
 *
 * @ingroup pantheon_next
 */
class PantheonNextEnvForm extends ContentEntityForm {

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
    unset($form['consumer'], $form['consumer'], $form['actions']['delete']);
    $entity = $this->entity;
    $request = \Drupal::request();
    $variables = [
      'NEXT_PUBLIC_DRUPAL_BASE_URL' => $request->getSchemeAndHttpHost(),
      'NEXT_IMAGE_DOMAIN' => $request->getHost(),
      'DRUPAL_SITE_ID' => $entity->getNextSite()->id(),
      'DRUPAL_FRONT_PAGE' => \Drupal::config('system.site')->get('page.front'),
    ];

    if ($secret = $entity->getNextSite()->getPreviewSecret()) {
      $variables += [
        'DRUPAL_PREVIEW_SECRET' => '**************',
        'DRUPAL_CLIENT_ID' => $entity->getConsumer()->uuid(),
        'DRUPAL_CLIENT_SECRET' => '**************',
      ];
    }

    $form['container'] = [
      '#title' => $this->t('Environment variables'),
      '#type' => 'fieldset',
      '#title_display' => 'invisible',
    ];

    foreach ($variables as $name => $value) {
      $form['container'][$name] = [
        '#type' => 'inline_template',
        '#template' => '{{ name }}={{ value }}<br/>',
        '#context' => [
          'name' => $name,
          'value' => $value,
        ]
      ];
    }

    $form['description'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Copy and paste these values in your <em>.env</em> or <em>.env.local</em> files.'),
    ];
    $form['actions']['submit']['#value'] = $this->t('Generate keys');
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

}
