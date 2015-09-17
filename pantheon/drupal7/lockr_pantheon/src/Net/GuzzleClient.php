<?php

/**
 * @file
 * Contains Lockr\Net\GuzzleClient.
 */

namespace Lockr\Net;

/**
 * Implements ClientInterface for guzzle.
 */
class GuzzleClient implements ClientInterface {

  /**
   * @var \GuzzleHttp\Client $client
   * The wrapped guzzle client.
   */
  protected $client;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $options) {
    $this->client = new \GuzzleHttp\Client($options);
  }

  /**
   * {@inheritdoc}
   */
  public function call(\Lockr\Call $call, array $options) {
    try {
      $response = $this->client->{$call->method}($call->path, $options);
    }
    catch (\GuzzleHttp\Exception\RequestException $e) {
      if ($e->hasResponse()) {
        return new GuzzleResponse($e->getResponse());
      }
      throw new \Lockr\Exceptions\NetworkException();
    }
    return new GuzzleResponse($response);
  }

}
