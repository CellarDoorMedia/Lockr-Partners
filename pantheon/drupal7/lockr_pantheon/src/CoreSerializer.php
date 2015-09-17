<?php

/**
 * @file
 * Contains Lockr\CoreSerializer.
 */

namespace Lockr;

class CoreSerializer implements SerializerInterface {

  /**
   * {@inheritdoc}
   */
  public function encode($data) {
    return json_encode($data);
  }

  /**
   * {@inheritdoc}
   */
  public function decode($data) {
    return json_decode($data);
  }

}
