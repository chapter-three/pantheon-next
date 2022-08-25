<?php

namespace Drupal\pantheon_next\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\pantheon_next\NextInstaller;

class PantheonNextInstallerForm extends FormBase {

  public function getFormId() {
    return 'pantheon_next_installer_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['intro'] = [
      '#type' => 'inline_template',
      '#template' => '<h4>Welcome to Next.js for Pantheon</h4>
<p>This wizard will help you configure Next.js for Pantheon Decoupled.</p>
<p>Running the installer will automatically sets everything you need to run a Next.js site:</p>
<ul>
<li>Create a Next.js site.</li>
<li>Configure a consumer for authenticating JSON:API requests.</li>
<li>Configure decoupled preview for the following content types: Article, Recipe and Basic Page.</li>
</ul>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Run Installer'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\pantheon_next\NextInstaller $installer */
    $installer = \Drupal::service('pantheon_next.installer');
    $installer->run();

    // Set the default theme to gin.
    \Drupal::configFactory()->getEditable('system.theme')->set('default', 'gin')->save();

    \Drupal::messenger()->addStatus($this->t('You have successfully created your first Next.js site.'));
  }

}
