<?php

/**
 * @file
 * Contains Lockr\Net\CurlClient.
 */

namespace Lockr\Net;

/**
 * Client wrapping a procedural cURL call.
 */
class CurlClient implements ClientInterface {

  /**
   * @var array $options
   * Options used to create this client.
   */
  protected $options;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $options) {
    $this->options = $options;
  }

  /**
   * {@inheritdoc}
   */
  public function call(\Lockr\Call $call, array $options) {
    $path = rtrim($this->options['base_uri'], '/') . '/' . ltrim($call->path, '/');
    $port = strtolower(substr($path, 0, 5)) === 'https' ? 443 : 80;
    $opts = array(
      CURLOPT_URL => $path,
      CURLOPT_PORT => $port,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_CUSTOMREQUEST => strtoupper($call->method),
      CURLOPT_SSLCERT => $options['cert'],
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'X-Ignore-Agent: 1',
      ),
    );
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    if ($call->auth) {
      curl_setopt($ch, CURLOPT_USERPWD, implode(':', $call->auth));
    }
    if (isset($options['body'])) {
      if ($call->method === 'post') {
        curl_setopt($ch, CURLOPT_POST, 1);
      }
      curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body']);
    }
    $result = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (curl_errno($ch) != 0) {
      $error = curl_error($ch);
      curl_close($ch);
      return new CurlResponse($result, $status);
    }
    curl_close($ch);
    return new CurlResponse($result, $status);
  }

}
