<?php

namespace Drupal\pantheon_next\Entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\InvokeCommand;

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
    ];

    $secret = $entity->getNextSite()->getPreviewSecret();
    $variables += [
      'DRUPAL_PREVIEW_SECRET' => '<span id="drupal-preview-secret">' . $secret . '</span>',
      'DRUPAL_CLIENT_ID' => $entity->getConsumer()->uuid(),
      'DRUPAL_CLIENT_SECRET' => '<span id="drupal-client-secret">**************</span>',
    ];

    $form['container'] = [
      '#title' => $this->t('Environment variables'),
      '#type' => 'fieldset',
      '#title_display' => 'invisible',
    ];

    foreach ($variables as $name => $value) {
      $form['container'][$name] = [
        '#type' => 'inline_template',
        '#template' => '{{ name }}={{ value|raw }}<br/>',
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
    $form['confirm'] = [
      '#type' => 'checkbox',
      '#default_value' => TRUE,
      '#title' => $this->t('Invalidate the current preview and client secrets'),
      //'#required' => TRUE,
    ];
    $form['actions']['submit']['#value'] = $this->t('Generate New Secret');
    $form['actions']['submit']['#ajax'] = [
      'callback' => [$this, 'generateSecret'],
      'wrapper'  => 'product-container',
    ];
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
   * Ajax callback to generate secrets.
   */
  public function generateSecret(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if ($form_state->getValue('confirm')) {
      $password_generator = \Drupal::service('password_generator');
      $preview_secret = $password_generator->generate(21);
      $client_secret = $password_generator->generate(21);
      $next_site = $this->entity->getNextSite();
      $next_site->setPreviewSecret($preview_secret);
      $next_site->save();
      $consumer = $this->entity->getConsumer();
      $consumer->set('secret', $client_secret);
      $consumer->save();
      $response->addCommand(new InvokeCommand("#drupal-preview-secret", 'html', [$preview_secret]));
      $response->addCommand(new InvokeCommand("#drupal-client-secret", 'html', [$client_secret]));
    }
    return $response;
  }

}
