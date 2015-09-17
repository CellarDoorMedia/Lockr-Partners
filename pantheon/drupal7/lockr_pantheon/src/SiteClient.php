<?php

/**
 * @file
 * Contains \Lockr\SiteClient.
 */

namespace Lockr;

/**
 * Client to register sites.
 */
class SiteClient extends Client {

  /**
   * @const BASE_URI
   * The client base URI.
   */
  const BASE_URI = 'https://api.getlockr.io/api/v1.0/site';

  /**
   * @const ALREADY_EXISTS
   * Returns from register if the email requested already exists or
   * authentication failed.
   */
  const ALREADY_EXISTS = 'E05';

  /**
   * @const UNKNOWN_ERROR
   * Returns from register if there was a server error.
   */
  const UNKNOWN_ERROR = 'E01';

  /**
   * Check if this site is registered.
   *
   * @return bool
   * TRUE if this site is registered with Lockr, FALSE otherwise.
   */
  public function exists() {
    $response = $this->call(new Call('exists'));
    if ($response->getStatus() !== 200) {
      return FALSE;
    }
    $data = $this->decode($response->getBody());
    return (array) $data->data;
  }

  /**
   * Register a new account with email and register site.
   *
   * @param string $email
   * The user email.
   * @param array $pass
   * The user password to authenticate with.
   *
   * @return bool | string
   * TRUE if the user and site are created, a string if there is an
   * issue.
   */
  public function register($email, $pass = NULL) {
    $options = array(
      'method' => 'post',
      'data' => array(
        'email' => $email,
        'name' => variable_get('site_name'),
      ),
    );
    if ($pass !== NULL) {
      $options['auth'] = array($email, $pass);
    }
    $response = $this->call(new Call('register', $options));
    if ($response === FALSE) {
      return self::UNKNOWN_ERROR;
    }
    if ($response->getStatus() === 200) {
      return TRUE;
    }
    $body = $this->decode($response->getBody());
    if (substr($body->title, 0, 3) === self::ALREADY_EXISTS) {
      return self::ALREADY_EXISTS;
    }
  }

}
