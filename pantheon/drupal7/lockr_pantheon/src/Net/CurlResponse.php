<?php

/**
 * @file
 * Contains Lockr\Net\CurlResponse.
 */

namespace Lockr\Net;

/**
 * Response from the CurlClient.
 */
class CurlResponse implements ResponseInterface {

  /**
   * @var string $body
   * The response body.
   */
  protected $body;

  /**
   * @var int $code
   * The status code.
   */
  protected $code;

  /**
   * Constructs a curl response from a body.
   *
   * @param string $body
   * The response body.
   * @param int $code
   * The response status code.
   */
  public function __construct($body, $code) {
    $this->body = $body;
    $this->code = $code;
  }

  /**
   * {@inheritdoc}
   */
  public function getBody() {
    return $this->body;
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->code;
  }

}
