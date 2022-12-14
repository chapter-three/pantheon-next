<?php

namespace Drupal\next_for_drupal_pantheon\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\next_for_drupal_pantheon\PantheonNextInstallerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Declares a form for running the next_for_drupal_pantheon installer.
 */
class PantheonNextInstallerForm extends FormBase {

  /**
   * The next_for_drupal_pantheon installer service.
   *
   * @var \Drupal\next_for_drupal_pantheon\PantheonNextInstallerInterface
   */
  protected PantheonNextInstallerInterface $pantheonNextInstaller;

  /**
   * Constructs the PantheonNextInstallerForm form.
   *
   * @param \Drupal\next_for_drupal_pantheon\PantheonNextInstallerInterface $next_for_drupal_pantheon_installer
   *   The next_for_drupal_pantheon installer service.
   */
  public function __construct(PantheonNextInstallerInterface $next_for_drupal_pantheon_installer) {
    $this->pantheonNextInstaller = $next_for_drupal_pantheon_installer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('next_for_drupal_pantheon.installer'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'next_for_drupal_pantheon_installer_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['intro'] = [
      '#type' => 'inline_template',
      '#template' => '
        <h4>Welcome to Next.js for Pantheon</h4>
        <p>This wizard will help you configure Next.js for Pantheon Decoupled.</p>
        <p>Running the installer will automatically set everything you need to run a Next.js site:</p>
        <ul>
          <li>Create a Next.js site.</li>
          <li>Configure a consumer for authenticating JSON:API requests.</li>
          <li>Configure decoupled preview for content types.</li>
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
    $this->pantheonNextInstaller->run();
    $this->messenger()
      ->addStatus($this->t('You have successfully created your first Next.js site.'));
    $this->messenger->addWarning($this->t('Remember to update the email address for the newly created Next.js user.'));
  }

}
