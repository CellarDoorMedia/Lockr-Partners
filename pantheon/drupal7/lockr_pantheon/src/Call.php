<?php

/**
 * @file
 * Contains Lockr\Call.
 */

namespace Lockr;

/**
 * Represents an API call to the Lockr platform.
 */
class Call {

  /**
   * @var string $path
   * The request path.
   */
  public $path;

  /**
   * @var string $method
   * The request method.
   */
  public $method;

  /**
   * @var any $data
   * The request payload.
   */
  public $data;

  /**
   * @var array $params
   * Mapping of param name to param value.
   */
  public $params = array();

  /**
   * @var array $auth
   * Credentials for HTTP basic auth.
   */
  public $auth = FALSE;

  /**
   * Constructs a Call from parameters.
   *
   * @param string $path
   * (optional) The request path.
   * @param array $options
   * (optional) Can include:
   * - method (one of get, delete, head, options, patch, post, or put)
   * - data
   * - params
   */
  public function __construct($path, array $options = array()) {
    $this->path = $path;
    $this->method = isset($options['method'])
      ? strtolower($options['method'])
      : 'get';
    $this->data = isset($options['data']) ? $options['data'] : NULL;
    $this->params = isset($options['params']) ? $options['params'] : array();
    $this->auth = isset($options['auth']) ? $options['auth'] : FALSE;
  }

}
