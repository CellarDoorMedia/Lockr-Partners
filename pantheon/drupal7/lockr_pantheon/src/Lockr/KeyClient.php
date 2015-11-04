<?php
// ex: ts=4 sts=4 sw=4 et:

namespace Lockr;

use Lockr\Exception\ClientException;
use Lockr\Exception\ServerException;

class KeyClient
{
    /**
     * @var Lockr The external interface.
     */
    protected $client;

    /**
     * @var string|bool Data to decrypt keys.
     */
    protected $encoded = null;

    /**
     * Constructs a SiteClient.
     *
     * @param Lockr $client The external interface.
     */
    public function __construct(Lockr $client)
    {
        $this->client = $client;
    }

    /**
     * Gets and sets encrypted keys.
     *
     * @param string $encoded (optional) Data to decrypt keys.
     *
     * @return self The client for method chaining.
     */
    public function encrypted($encoded = true)
    {
        $this->encoded = $encoded;
        return $this;
    }

    /**
     * Gets a key from Lockr.
     *
     * @param string $name The key name.
     *
     * @return string The key.
     */
    public function get($name)
    {
        list($status, $body) = $this->client->get($this->uri($name));

        $body = json_decode($body, true);

        $body_error = (json_last_error() !== JSON_ERROR_NONE)
            || !isset($body['data']);
        if ($body_error || $status >= 500) {
            throw new ServerException();
        }
        if ($status >= 400) {
            throw new ClientException();
        }

        $data = $body['data'];

        if (null !== $this->encoded) {
            return $this->decrypt($data['key_value'], $this->encoded);
        }
        return $data['key_value'];
    }

    /**
     * Sets a key in Lockr.
     *
     * @param string $name The key name.
     * @param string $value The key value.
     * @param string $label The key label.
     *
     * @return string Returns the decrypt data or true.
     */
    public function set($name, $value, $label)
    {
        if ($this->encoded) {
            list($value, $encoded) = $this->encrypt($value);
        }
        $data = array(
            'key_value' => $value,
            'key_label' => $label,
        );
        list($status, $_) = $this->client->patch($this->uri($name), $data);

        if ($status >= 500) {
            throw new ServerException();
        }
        if ($status >= 400) {
            throw new ClientException();
        }

        if ($this->encoded) {
            return $encoded;
        }
        return true;
    }

    /**
     * Deletes a key from Lockr.
     *
     * @param string $name The key name.
     */
    public function delete($name)
    {
        $this->client->delete($this->uri($name));
    }

    protected function uri($name)
    {
        return '/v1.0/key/'.urlencode($name);
    }

    protected function encrypt($plaintext)
    {
        $key = openssl_random_pseudo_bytes(32);

        $cipher = MCRYPT_RIJNDAEL_256;
        $mode = MCRYPT_MODE_CBC;

        $iv_len = mcrypt_get_iv_size($cipher, $mode);
        $iv = mcrypt_create_iv($iv_len);

        $ciphertext = mcrypt_encrypt($cipher, $key, $plaintext, $mode, $iv);

        $parts = array(
            $cipher,
            $mode,
            base64_encode($iv),
            base64_encode($key),
        );

        $ciphertext = base64_encode($ciphertext);
        $encoded = implode('$', $parts);

        return array($ciphertext, $encoded);
    }

    protected function decrypt($ciphertext, $encoded)
    {
        list($cipher, $mode, $iv, $key) = explode('$', $encoded, 4);
        $iv = base64_decode($iv);
        $ciphertext = base64_decode($ciphertext);
        $key = base64_decode($key);

        $key = mcrypt_decrypt($cipher, $key, $ciphertext, $mode, $iv);

        return trim($key);
    }
}
