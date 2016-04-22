<?php
// ex: ts=4 sts=4 sw=4 et:

namespace Lockr;

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
