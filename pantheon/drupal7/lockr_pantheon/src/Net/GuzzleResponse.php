<?php

/**
 * @file
 * Contains Lockr\Net\GuzzleResponse.
 */

namespace Lockr\Net;

/**
 * Wraps a response from guzzle.
 */
class GuzzleResponse implements ResponseInterface {

  /**
   * @var \GuzzleHttp\Psr7\Response $response
   * The wrapped response.
   */
  protected $response;

  /**
   * Constructs a new response.
   *
   * @param \GuzzleHttp\Psr7\Response $response
   * The response to wrap.
   */
  public function __construct(\GuzzleHttp\Psr7\Response $response) {
    $this->response = $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getBody() {
    return $this->response->getBody();
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->response->getStatusCode();
  }

}
