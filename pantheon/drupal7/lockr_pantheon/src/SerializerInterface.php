<?php

/**
 * @file
 * Contains Lockr\SerializerInterface.
 */

namespace Lockr;

interface SerializerInterface {

  /**
   * Encodes arbitrary data to JSON.
   *
   * @param mixed $data
   * The data to encode.
   *
   * @return string
   * Encoded data.
   */
  public function encode($data);

  /**
   * Decodes a JSON string into a PHP value.
   *
   * @param string $data
   * Encoded data.
   *
   * @return mixed
   * Decoded data.
   */
  public function decode($data);

}
