<?php

/**
 * @file
 * Contains Lockr\Net\ClientInterface.
 */

namespace Lockr\Net;

/**
 * Provides an interface for the Lockr clients to talk through.
 *
 * This is to allow multiple client backends. Namely curl and guzzle.
 */
interface ClientInterface {

  /**
   * Constructs a client with options.
   *
   * @param array $options
   * Options passed into the client.
   */
  public function __construct(array $options);

  /**
   * Executes a \Lockr\Call object.
   *
   * @param \Lockr\Call $call
   * The call object.
   * @param array $options
   * Options to change the call's behavior.
   *
   * @return \Lockr\Net\Response.
   * The response object.
   */
  public function call(\Lockr\Call $call, array $options);

}
