<?php

/**
 * @file
 * Contains Drupal\lockr_pantheon\LockrLoginForm.
 */

namespace Drupal\lockr_pantheon;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

use Drupal\lockr_pantheon\Lockr\Exception\ClientException;
use Drupal\lockr_pantheon\Lockr\Exception\ServerException;
use Drupal\lockr_pantheon\Lockr\Lockr;

/**
 */
class LockrLoginForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lockr_login';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    list($exists, $_) = Lockr::site()->exists();

    if ($exists) {
      $form['registered'] = [
        '#title' => $this->t("You're already registered"),
        '#prefix' => '<p>',
        '#markup' => $this->t(
          "This site is already registered with the Lockr key management service. " .
          "There's nothing left for you to do here, " .
          'your keys entered in the key settings are already protected. ' .
          'If you registered with the wrong account, you can ' .
          'click <a href="@link" target="_blank">here</a> to go to Lockr and manage your sites.',
          ['@link' => 'https://lockr.io/user/login']
        ),
        '#suffix' => '</p>',
      ];

      return $form;
    }

    $default_email = isset($_GET['email'])
      ? $_GET['email']
      : NULL;

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#default_value' => $default_email,
      '#description' => $this->t('Enter your @s email.', ['@s' => 'Lockr']),
    ];

    $form['pass'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
      '#description' => $this->t('Enter the password that accompanies your email.'),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Log in'),
    ];

    $next = isset($_GET['next'])
      ? $_GET['next']
      : 'entity.key.collection';

    $form_state->set('next', $next);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    if (!\Drupal::service('email.validator')->isValid($email)) {
      $form_state->setErrorByName('email', $this->t('Please enter a valid email address'));
    }
  }

  /**
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    $pass = $form_state->getValue('pass');
    $name = \Drupal::config('system.site')->get('name');
    try {
      Lockr::site()->register($email, $pass, $name);
    }
    catch (ClientException $e) {
      drupal_set_message($this->t('Login credentials incorrect, please try again.'), 'error');
      return;
    }
    catch (ServerException $e) {
      drupal_set_message($this->t('An unknown error has occurred, please try again later.'), 'error');
      return;
    }

    drupal_set_message($this->t("That's it! This site has been added to Lockr; your keys are now safe."));
    $form_state->setRedirect($form_state->get('next'));
  }

}
