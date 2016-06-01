<?php

namespace Lockr;

// Don't call the file directly and give up info!
if ( !function_exists( 'add_action' ) ) {
	echo 'Lock it up!';
	exit;
}

// ex: ts=4 sts=4 sw=4 et:

class Partner implements PartnerInterface
{
    /**
     * @var string The SSL cert path.
     */
    protected $cert;

    /**
     * @var string The Lockr partner.
     */
    protected $partner;

    /**
     * Constucts the partner.
     */
    public function __construct($cert, $partner)
    {
        $this->cert = $cert;
        $this->partner = $partner;
    }

    /**
     * {@inheritdoc}
     */
    public function requestOptions()
    {
        return array(
            'cert' => $this->cert,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getReadUri()
    {
        return "https://{$this->partner}.api.lockr.io";
    }

    /**
     * {@inheritdoc}
     */
    public function getWriteUri()
    {
        return "https://{$this->partner}.api.lockr.io";
    }
}
