<?php
// ex: ts=4 sts=4 sw=4 et:

namespace Drupal\lockr_pantheon\Lockr;

class Partner implements PartnerInterface
{
    /**
     * @var string The SSL cert path.
     */
    protected $cert;

    /**
     * Constucts the Pantheon partner.
     */
    public function __construct()
    {
        $this->cert = '/srv/bindings/' . PANTHEON_BINDING . '/certs/binding.pem';
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
        return 'https://pantheon.api.lockr.io';
    }

    /**
     * {@inheritdoc}
     */
    public function getWriteUri()
    {
        return 'https://pantheon.api.lockr.io';
    }
}
