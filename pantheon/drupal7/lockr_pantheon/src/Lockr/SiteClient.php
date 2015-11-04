<?php
// ex: ts=4 sts=4 sw=4 et:

namespace Lockr;

use Lockr\Exception\ClientException;
use Lockr\Exception\ServerException;

/**
 * API for site management operations.
 */
class SiteClient
{
    /**
     * @var Lockr The external interface.
     */
    protected $client;

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
     * Checks if the current site/env is registered and/or available.
     *
     * @return bool[] Returns a two-value array of booleans:
     *
     * - True if the site is registered with Lockr.
     * - True if the current env is available.
     *
     * @throws ServerException
     * if the server is unavailable or returns an error.
     * @throws ClientException if there was an unexpected client error.
     */
    public function exists()
    {
        list($status, $body) = $this->client->get('/v1.0/site/exists');

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
        return array(
            isset($data['exists']) ? (bool) $data['exists'] : false,
            isset($data['available']) ? (bool) $data['available'] : false,
        );
    }

    /**
     * Registers the site with Lockr.
     *
     * @param string $email The email to register with.
     * @param string $pass  (optional) The password for authentication.
     * @param string $name  (optional) The site name.
     *
     * @throws ServerException
     * if the server is unavailable or returns an error.
     * @throws ClientException if there was an unexpected client error.
     */
    public function register($email, $pass = null, $name = null) {
        $data = array(
            'email' => $email,
            'name' => $name,
        );

        if (null !== $pass) {
            $auth = "$email:$pass";
        } else {
            $auth = null;
        }

        list($status, $_) = $this->client->post(
            '/v1.0/site/register',
            $data,
            $auth
        );

        if ($status >= 500) {
            throw new ServerException();
        }
        if ($status >= 400) {
            throw new ClientException();
        }
    }
}
