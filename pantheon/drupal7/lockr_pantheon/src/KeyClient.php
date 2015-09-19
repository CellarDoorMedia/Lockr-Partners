<?php

/**
 * @file
 * Contains Lockr\KeyClient.
 */

namespace Lockr;

/**
 * Client to handle requests to the key service.
 */
class KeyClient extends Client {

  /**
   * @const BASE_URI
   * The client base URI.
   */
  const BASE_URI = 'https://api.lockr.io/api/v1.0/key';

  /**
   * Gets a key value from the remote service.
   *
   * @param \Lockr\Key $key
   * The key to request.
   *
   * @return string | bool
   * The key value or FALSE on failure.
   */
  public function get(\Lockr\Key $key) {
    $response = $this->call($key->get());
    if ($response->getStatus() !== 200) {
      return FALSE;
    }
    $data = $this->decode($response->getBody());
    return $data->data->key_value;
  }

  /**
   * Sets a key value in the remote service.
   *
   * @param \Lockr\Key $key
   * The key to set.
   * @param string $key_value
   * The key value.
   *
   * @return bool
   * TRUE if the key was successfully set, FALSE otherwise.
   */
  public function set(\Lockr\Key $key, $key_value) {
    $response = $this->call($key->set($key_value));
    return $response->getStatus() === 200;
  }

  /**
   * Delets a key from the remote service.
   *
   * @param \Lockr\Key $key
   * The key to delete.
   */
  public function delete(\Lockr\Key $key) {
    $this->call($key->delete());
  }

  /**
   * Checks for a key's existence.
   *
   * @param \Lockr\Key $key
   * The key to check.
   *
   * @return bool
   * TRUE if the key exists, FALSE if not.
   */
  public function exists(\Lockr\Key $key) {
    $response = $this->call($key->exists());
    return $response->getStatus() === 200;
  }

}
