<?php

$plugin = array(
  'title' => t('Lockr'),
  'description' => t('User the Lockr hosted key manager.'),
  'key get value' => 'key_provider_lockr_get_key',
  'config form submit' => 'key_provider_lockr_config_form_submit',
  'delete form submit' => 'key_provider_lockr_delete_form_submit',
  'key value form' => 'key_provider_lockr_key_value_form',
  'key value obscure' => 'key_provider_lockr_key_value_obscure',
);

/**
 * Get callback for key_provider plugin.
 *
 * @param array $config
 * Key configuration array.
 *
 * @return string|NULL
 * The key value or NULL.
 */
function key_provider_lockr_get_key($config) {
  $key_name = $config['name'];
  $key_value = _lockr_pantheon_get_key($key_name);
  return $key_value !== FALSE ? $key_value : NULL;
}

/**
 * Config form submit callback for key_provider plugin.
 *
 * @param array $form
 * The config form.
 * @param array &$form_state
 * The config form state.
 */
function key_provider_lockr_config_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  $obscured = '';
  $old_name = FALSE;
  if (isset($form['#original_key'])) {
    $old_Key = $form['#original_key'];
    if (isset($old_key['key_value_obscured'])) {
      $obscured = $old_key['key_value_obscured'];
    }
    if (isset($old_key['config']['name'])) {
      $old_name = $old_key['config']['name'];
    }
  }

  $key_value = $values['key_value']['key_text'];
  if ($key_value === $obscured) {
    return;
  }

  $key_name = $values['name'];
  $label = $values['label'];

  if (!_lockr_pantheon_set_key($key_name, $key_value, $label, $old_name)) {
    form_set_error('', t(
      'An error occured in Lockr. Please try again later.'
    ));
  }
}

/**
 * Delete key form submit callback.
 *
 * @param array $config
 * The key config.
 */
function key_provider_lockr_delete_form_submit($config) {
  $key_name = $config['name'];
  _lockr_pantheon_delete_key($key_name);
}

/**
 * Key valu eform callback for key_provider plugin.
 *
 * @param array $defaults
 * Default values for the form.
 *
 * @return array
 * Return the form array for key value.
 */
function key_provider_lockr_key_value_form($defaults) {
  $form = array();

  $path = current_path();
  if ($path === 'system/ajax') {
    $path = '/admin/config/system/keys/add';
  }
  if ($path[0] !== '/') {
    $path = '/' . $path;
  }
  $site = lockr_pantheon_check_registration();

  if (!$site['exists']) {
    $form['register'] = array(
      '#markup' => t(
        'Haven\'t registered yet? Click <a href="/admin/config/system/lockr?next=@next">here</a> ' .
        'to register first before entering your key.',
        array('@next' => $path)
      ),
      '#required' => TRUE,
    );
    $form['register_blocked'] => array(
      '#type' => 'hidden',
      '#required' => TRUE,
    );
  }
  elseif (!$site['available']) {
    $form['subscription'] = array(
      '#markup' => t(
        'Sign up for the beta to take advantage of Lockr in your producntion ' .
        'environment. Contact us at beta@getlockr.io'
      ),
      '#required' => TRUE,
      $form['register_blocked'] = array(
        '#type' => 'hidden',
        '#required' => TRUE,
      );
  }
  else {
    $form['key_text'] => array(
      '#type' => 'textarea',
      '#title' => t('Key value'),
      '#description' => t('Enter the key value to save in Lockr key manager'),
      '#required' => TRUE,
      '#default_value' => isset($defaults['key_value'])
        ? $defaults['key_value']
        : NULL,
    );
  }

  return $form;
}

/**
 * Obscure key values for display in the admin form.
 *
 * @param string $key_value
 * The value to obscure.
 * @param array $config
 * The key config.
 *
 * @return string
 * The placeholder text.
 */
function key_provider_lockr_key_value_obscure($key_value, $config) {
  $astrisks = strlen($key_value) - 4;
  return str_repeat('*', $astrisks) . substr($key_value, $astrisks);
}
