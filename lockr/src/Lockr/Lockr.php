<?php

namespace Lockr;

// Don't call the file directly and give up info!
if ( !function_exists( 'add_action' ) ) {
	echo 'Lock it up!';
	exit;
}

// ex: ts=4 sts=4 sw=4 et:

/**
 * Primary interface for Lockr API calls.
 */
class Lockr
{
    /**
     * @const The current client version.
     */
    const VERSION = '0.1.0';

    /**
     * @var The partner instance.
     */
    protected static $instance = null;

    /**
     * @var The partner client.
     */
    protected $partner;

    /**
     * Constructs Lockr with a partner.
     *
     * @param PartnerInterface $partner The partner client.
     */
    public function __construct(PartnerInterface $partner)
    {
        $this->partner = $partner;
    }

    /**
     * Returns the client for site operations.
     *
     * @return SiteClient The site client.
     */
    public static function site()
    {
        return new SiteClient(self::create());
    }

    /**
     * Returns the client for key operations.
     *
     * @return KeyClient The key client.
     */
    public static function key()
    {
        return new KeyClient(self::create());
    }

    /**
     * Get the partner instance.
     *
     * @return static The partner instance.
     */
    public static function create(PartnerInterface $partner = null)
    {
        if (null == static::$instance) {
            if ($partner !== null) {
                static::$instance = new static($partner);
            } else {
                static::$instance = new static(new Partner());
            }
        }

        return static::$instance;
    }

    public function get($uri)
    {
        $uri = $this->partner->getReadUri().$uri;
        return $this->request('GET', $uri, $this->partner->requestOptions());
    }

    public function head($uri)
    {
        $uri = $this->partner->getReadUri().$uri;
        return $this->request('HEAD', $uri, $this->partner->requestOptions());
    }

    public function post($uri, $data, $auth = null)
    {
        $uri = $this->partner->getWriteUri().$uri;
        $options = $this->partner->requestOptions();
        $options['data'] = $data;
        if (null !== $auth) {
            $options['auth'] = $auth;
        }
        return $this->request('POST', $uri, $options);
    }

    public function patch($uri, $data)
    {
        $uri = $this->partner->getWriteUri().$uri;
        $options = $this->partner->requestOptions();
        $options['data'] = $data;
        return $this->request('PATCH', $uri, $options);
    }

    public function delete($uri)
    {
        $uri = $this->partner->getWriteUri().$uri;
        return $this->request(
            'DELETE',
            $uri,
            $this->partner->requestOptions()
        );
    }

    protected function request($method, $uri, array $options = array())
    {
        $opts = array(
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PORT           => 443,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_URL            => $uri,
            CURLOPT_HTTPHEADER     => array(
                'Content-Type:',
            ),
        );

        if (in_array($method, array('POST', 'PATCH'))) {
            $data = json_encode($options['data']);
            $opts[CURLOPT_POSTFIELDS] = $data;
            $opts[CURLOPT_HTTPHEADER] = array(
                'Content-Type: application/json',
                'Content-Length: '.strlen($data),
            );
        }

        if (isset($options['cert'])) {
            $opts[CURLOPT_SSLCERT] = $options['cert'];
        }

        if (isset($options['auth'])) {
            $opts[CURLOPT_USERPWD] = $options['auth'];
        }

        $ch = curl_init();
        curl_setopt_array($ch, $opts);

        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return array($code, $resp);
    }
}
