<?php

/**
 * @file
 * Contains Lockr\Client.
 */

namespace Lockr;

/**
 * Lockr\Client is a relatively simple wrapper around guzzle with some
 * state for convenience.
 */
class Client {

  /**
   * @static DEFAULT_CLIENT
   * The default client, varies by PHP version.
   */
  static $DEFAULT_CLIENT = '\Lockr\Net\CurlClient';

  /**
   * @static DEFAULT_SERIALIZER
   * The default serializer.
   */
  static $DEFAULT_SERIALIZER = '\Lockr\CoreSerializer';

  /**
   * @var string $certPath
   * Path to the client certificate.
   */
  public static $certPath;

  /**
   * @var \Lockr\Net\ClientInterface $client
   * The client wrapper.
   */
  protected $client;

  /**
   * @var \Lockr\SerializerInterface $serializer
   * The data serializer.
   */
  protected $serializer;

  /**
   * Constructs a client.
   *
   * @param \Lockr\Net\ClientInterface $client
   * The lockr HTTP client.
   */
  public function __construct(\Lockr\Net\ClientInterface $client) {
    $this->client = $client;
    $this->serializer = new self::$DEFAULT_SERIALIZER();
  }

  /**
   * Creates a new KeyClient using defaults.
   */
  public static function create() {
    return new static(
      new self::$DEFAULT_CLIENT(array('base_uri' => static::BASE_URI))
    );
  }

  /**
   * Shortcut to encode data.
   *
   * @param mixed $data
   * The data to encode.
   *
   * @return string
   * The encoded data.
   */
  protected function encode($data) {
    return $this->serializer->encode($data);
  }

  /**
   * Shortcut to decode data.
   *
   * @param string $data
   * The data to decode.
   *
   * @return mixed
   * The decoded data.
   */
  protected function decode($data) {
    return $this->serializer->decode($data);
  }

  /**
   * Parse a \Lockr\Call and delegates to guzzle.
   *
   * Currently we just eat all exceptions.
   *
   * @param \Lockr\Call $call
   * The call.
   *
   * @return \Lockr\Net\ResponseInterface | bool
   * The response from the client or FALSE on failure.
   */
  protected function call(\Lockr\Call $call) {
    $options = array(
      'cert' => self::$certPath,
    );
    if ($call->data !== NULL) {
      $options['body'] = $this->encode($call->data);
    }
    if (!empty($call->params)) {
      $options['query'] = $call->params;
    }
    try {
      return $this->client->call($call, $options);
    }
    catch (\Lockr\Exceptions\NetworkException $e) {
      return FALSE;
    }
  }

}
