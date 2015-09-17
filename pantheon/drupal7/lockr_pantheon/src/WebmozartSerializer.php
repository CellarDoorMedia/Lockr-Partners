<?php

/**
 * @file
 * Contains Lockr\WebmozartSerializer.
 */

namespace Lockr;

class WebmozartSerializer implements SerializerInterface {

  /**
   * @var \Webmozart\Json\JsonEncoder $encoder
   * The json encoder.
   */
  protected $encoder;

  /**
   * @var \Webmozart\Json\JsonDecoder $decoder
   * The json decoder.
   */
  protected $decoder;

  /**
   * Constructs a new serializer.
   */
  public function __construct() {
    $this->encoder = new \Webmozart\Json\JsonEncoder();
    $this->decoder = new \Webmozart\Json\JsonDecoder();
  }

  /**
   * {@inheritdoc}
   */
  public function encode($data) {
    return $this->encoder->encode($data);
  }

  /**
   * {@inheritdoc}
   */
  public function decode($data) {
    return $this->decoder->decode($data);
  }

}
