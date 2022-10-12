<?php

namespace Drupal\next_for_drupal_pantheon\Entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * PantheonNextEnvForm controller.
 *
 * @ingroup next_for_drupal_pantheon
 */
class PantheonNextEnvForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return 'entity.next_for_drupal_pantheon.collection';
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
      'DRUPAL_PREVIEW_SECRET' => $secret,
      'DRUPAL_CLIENT_ID' => $entity->getConsumer()->uuid(),
      'DRUPAL_CLIENT_SECRET' => '**************',
    ];

    $form['description'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('Copy and paste the following environment variables on your Pantheon site dashboard. You can find the environment variables settings under Settings → Builds.'),
    ];
    $form['container'] = [
      '#title' => $this->t('Environment variables'),
      '#type' => 'fieldset',
      '#attributes' => ['class' => ['env-container']],
    ];
    $form['container']['labels'] = [
      '#type' => 'inline_template',
      '#template' => '<div>
        <div><h4 class="form-item__label">{{name_label}}</h4></div>
        <div><h4 class="form-item__label">{{value_label}}</h4></div>
      </div>',
      '#context' => [
        'name_label' => $this->t('Name'),
        'value_label' => $this->t('Value'),
      ],
    ];

    foreach ($variables as $name => $value) {
      $form['container'][$name] = [
        '#type' => 'container',
      ];
      $form['container'][$name]['name'] = [
        '#type' => 'textfield',
        '#value' => $name,
        '#attributes' => ['readonly' => 'readonly'],
      ];
      $form['container'][$name]['value'] = [
        '#type' => 'textfield',
        '#value' => $value,
        '#attributes' => [
          'readonly' => 'readonly',
          'class' => ['value-' . $name],
        ],
      ];
    }

    $form['confirm'] = [
      '#type' => 'checkbox',
      '#default_value' => TRUE,
      '#title' => $this->t('Invalidate the current preview and client secrets'),
    ];
    $form['actions']['submit']['#value'] = $this->t('Generate New Secret');
    $form['actions']['submit']['#ajax'] = [
      'callback' => [$this, 'generateSecret'],
      'wrapper' => 'product-container',
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

    $form['#attached']['library'][] = 'next_for_drupal_pantheon/next_for_drupal_pantheon.env_form';

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
      $response->addCommand(new InvokeCommand(".value-DRUPAL_PREVIEW_SECRET", 'val', [$preview_secret]));
      $response->addCommand(new InvokeCommand(".value-DRUPAL_CLIENT_SECRET", 'val', [$client_secret]));
    }
    return $response;
  }

}
