<?php

/**
 * @file
 * Contains Drupal\lockr_pantheon\Form\LockrRegisterForm.
 */

namespace Drupal\lockr_pantheon\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\lockr_pantheon\Lockr\Exception\ClientException;
use Drupal\lockr_pantheon\Lockr\Exception\ServerException;
use Drupal\lockr_pantheon\Lockr\Lockr;

class LockrRegisterForm extends FormBase {

  public function getFormId() {
    return 'lockr_register_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    list($exists, $available) = Lockr::site()->exists();

    if (!$exists) {
      $form['email'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Email address'),
        '#required' => TRUE,
      ];

      $form['actions'] = ['#type' => 'actions'];
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Sign up'),
      ];

      $destination = isset($_GET['destination'])
        ? $_GET['destination']
        : 'entity.key.collection';

      $form_state->set('destination', $destination);
    }
    else {
      $form['registered'] = [
        '#title' => $this->t("You're already registered"),
        '#prefix' => '<p>',
        '#markup' => $this->t(
          'This site is already registered with the Lockr key management service. ' .
          "There's nothing left for you to do here, " .
          'your keys entered in the key settings are already protected. ' .
          'If you registered with the wrong account, you can ' .
          'click <a href="@link" target="_blank">here</a> to go to Lockr and manage your sites.',
          ['@link' => 'https://lockr.io/user/login']
        ),
        '#suffix' => '</p>',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    if (!\Drupal::service('email.validator')->isValid($email)) {
      $form_state->setErrorByName(
        'email',
        $this->t('Please enter a valid email address.')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    $destination = $form_state->get('destination');
    $name = \Drupal::config('system.site')->get('name');
    try {
      Lockr::site()->register($email, null, $name);
    }
    catch (ClientException $e) {
      drupal_set_message($this->t(
        'This email is already registered with Lockr. ' .
        'Please login to register a new site.'
      ));
      $form_state->setRedirect(
        'lockr.login',
        [],
        ['query' => ['email' => $email, 'destination' => $destination]]
      );
      return;
    }
    catch (ServerException $e) {
      $form_state->setErrorByName(
        '',
        $this->t('An unknown error has occurred, please try again later.')
      );
      return;
    }

    drupal_set_message($this->t(
      "That's it! You're signed up with Lockr; your keys are now safe."
    ));
    $form_state->setRedirect($destination);
  }

}

