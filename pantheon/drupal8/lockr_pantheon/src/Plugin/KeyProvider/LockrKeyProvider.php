<?php

/**
 * @file
 * Contains Drupal\lockr_pantheon\Plugin\KeyProvider\LockrKeyProvider.
 */

namespace Drupal\lockr_pantheon\Plugin\KeyProvider;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

use Drupal\key\KeyInterface;
use Drupal\key\KeyProviderBase;

use Drupal\lockr_pantheon\Lockr\Lockr;

/**
 * Adds a key provider that allows a key to be stored in Lockr.
 *
 * @KeyProvider(
 *   id = "lockr",
 *   title = "Lockr",
 *   description = @Translation("Allows a key to be stored in the Lockr key management platform."),
 *   storage_method = "lockr",
 * )
 */
class LockrKeyProvider extends KeyProviderBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'encoded' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $route = \Drupal::service('current_route_match')->getRouteName();

    list($exists, $available) = Lockr::site()->exists();

    if (!$exists) {
      $form['register'] = [
        '#markup' => $this->t(
          'Haven\'t registered yet? Click <a href="@link">here</a> ' .
          'to register first before entering your key.',
          ['@link' => Url::fromRoute(
            'lockr.register',
            [],
            ['query' => ['next' => $route]]
          )->toString()]
        ),
        '#required' => TRUE,
      ];
      $form['register_blocked'] = [
        '#type' => 'hidden',
        '#required' => TRUE,
      ];
    }
    elseif (!$available) {
      $form['subscription'] = [
        '#markup' => $this->t(
          'Sign up for the beta to take advantage of Lockr in your production ' .
          'environment. Contact us at beta@lockr.io'
        ),
        '#required' => TRUE,
      ];
      $form['register_blocked'] = [
        '#type' => 'hidden',
        '#required' => TRUE,
      ];
    }
    else {
      $key = $form_state->get('key_entity');
      $form['key_value'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Key value'),
        '#default_value' => $key->isNew() ? NULL : $this->getKeyValue($key),
        '#description' => $this->t('Enter the key to save in the Lockr key management platform.'),
        '#required' => TRUE,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue('id');
    $value = $form_state->getValue('key_provider_settings')['key_value'];
    $label = $form_state->getValue('label');

    try {
      $encoded = Lockr::key()
        ->encrypted()
        ->set($name, $value, $label);
    }
    catch (\Exception $e) {
      return FALSE;
    }

    $this->configuration['encoded'] = $encoded;

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getKeyValue(KeyInterface $key) {
    $encoded = $this->configuration['encoded'];

    try {
      $value = Lockr::key()
        ->encrypted($encoded)
        ->get($key->id());
    }
    catch (\Exception $e) {
      return FALSE;
    }

    return $value;
  }

}
