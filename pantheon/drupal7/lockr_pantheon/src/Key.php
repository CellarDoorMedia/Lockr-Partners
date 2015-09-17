<?php

/**
 * @file
 * Contains Lockr\Key.
 */

namespace Lockr;

/**
 * Creates Calls for operations on keys.
 */
class Key {

  /**
   * @var string $keyLabel
   * The key human readable name.
   */
  protected $keyLabel;

  /**
   * @var string $keyName
   * The key name.
   */
  protected $keyName;

  /**
   * @var string $oldName
   * The previous name.
   */
  protected $oldName;

  /**
   * Constructs a Key.
   *
   * @param string $key_name
   * The key name.
   * @param string $key_label
   * (optional) The human readable label.
   * @param string $old_name
   * (optional) Marks the previous name for this key.
   */
  public function __construct(
    $key_name,
    $key_label = FALSE,
    $old_name = FALSE
  ) {
    $this->keyName = $key_name;
    $this->keyLabel = $key_label;
    $this->oldName = $old_name;
  }

  /**
   * Creates a key with a different name.
   *
   * @param string $new_name
   * The new name.
   *
   * @return \Lockr\Key
   * A key with the new name.
   */
  public function changeName($new_name) {
    return new static(
      $new_name,
      $this->keyName
    );
  }

  /**
   * Returns the relative path for this key.
   *
   * @return string
   * The path.
   */
  public function path() {
    return urlencode($this->keyName);
  }

  /**
   * Partial function for the Call constructor.
   *
   * @param array $options
   * Options to pass into the Call constructor.
   *
   * @return \Lockr\Call
   * The call object.
   */
  protected function getCall(array $options = array()) {
    return new Call($this->path(), $options);
  }

  /**
   * Returns a Call to get this key.
   *
   * @return \Lockr\Call
   * The Call object.
   */
  public function get() {
    return $this->getCall();
  }

  /**
   * Returns a Call to set this key's value.
   *
   * @param string $key_value
   * The key's new value.
   *
   * @return \Lockr\Call
   * The call object.
   */
  public function set($key_value) {
    $data = array(
      'key_value' => $key_value,
    );
    if ($this->keyLabel) {
      $data['key_label'] = $this->keyLabel;
    }
    if ($this->oldName !== FALSE) {
      $data['old_name'] = $this->oldName;
    }
    return $this->getCall(array('method' => 'patch', 'data' => $data));
  }

  /**
   * Returns a Call to delete this key.
   *
   * @return \Lockr\Call
   * The call object.
   */
  public function delete() {
    return $this->getCall(array('method' => 'delete'));
  }

  /**
   * Returns a Call to check for a key's existence.
   *
   * @return \Lockr\Call
   * The call object.
   */
  public function exists() {
    return $this->getCall(array('method' => 'head'));
  }

}
