<?php

/**
 * @file
 * Contains Lockr\Net\ResponseInterface.
 */

namespace Lockr\Net;

/**
 * Defines simple functions for use by Lockr clients.
 */
interface ResponseInterface {

  /**
   * Returns the body of a response.
   *
   * @return string
   * The response body.
   */
  public function getBody();

  /**
   * Returns the status code of a response.
   *
   * @return int
   * The response code.
   */
  public function getStatus();

}
