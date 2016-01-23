<?php

/**
 * @file
 * Contains Drupal\lockr_pantheon\Plugin\KeyProvider\LockrKeyProvider.
 */

namespace Drupal\lockr_pantheon\Plugin\KeyProvider;

use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

use Drupal\key\KeyInterface;
use Drupal\key\Plugin\KeyPluginFormInterface;
use Drupal\key\Plugin\KeyProviderBase;
use Drupal\key\Plugin\KeyProviderSettableValueInterface;

use Drupal\lockr_pantheon\Lockr\Lockr;

/**
 * Adds a key provider that allows a key to be stored in Lockr.
 *
 * @KeyProvider(
 *   id = "lockr",
 *   label = "Lockr",
 *   description = @Translation("The Lockr key provider stores the key in Lockr key management service."),
 *   storage_method = "lockr",
 *   key_value = {
 *     "accepted" = TRUE,
 *     "required" = TRUE
 *   }
 * )
 */
class LockrKeyProvider extends KeyProviderBase
  implements KeyProviderSettableValueInterface, KeyPluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['encoded' => ''];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(
    array $form,
    FormStateInterface $form_state
  ) {
    list($exists, $_) = Lockr::site()->exists();

    if (!$exists) {
      $form['need_register'] = [
        '#prefix' => '<p>',
        '#markup' => $this->t(
          'This site has not yet registered with Lockr, ' .
          'please <a href="@link">click here to register</a>.',
          ['@link' => Url::fromRoute('lockr.register')->toString()]
        ),
        '#suffix' => '</p>',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(
    array &$form,
    FormStateInterface $form_state
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(
    array &$form,
    FormStateInterface $form_state
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function getKeyValue(KeyInterface $key) {
    $name = $key->id();
    $encoded = $this->getConfiguration()['encoded'];
    $client = Lockr::key()->encrypted($encoded);
    try {
      return $client->get($name);
    }
    catch (Exception $e) {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setKeyValue(KeyInterface $key, $key_value) {
    $name = $key->id();
    $label = $key->label();
    $client = Lockr::key()->encrypted();
    try {
      $encoded = $client->set($name, $key_value, $label);
    }
    catch (Exception $e) {
      return FALSE;
    }
    $this->setConfiguration(['encoded' => $encoded]);
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteKeyValue(KeyInterface $key) {
    $name = $key->id();
    $client = Lockr::key();
    try {
      $client->delete($name);
    }
    catch (Exception $e) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function obscureKeyValue($key_value, array $options = []) {
    return $key_value;
  }

}

