<?php
// ex: ts=4 sts=4 sw=4 et:

namespace Lockr;

/**
 * Interface to Lockr platform specific partners.
 */
interface PartnerInterface
{
    /**
     * Adds required request options to the request for partner authentication.
     */
    public function requestOptions();

    /**
     * Gets the partner base URI for reading data.
     */
    public function getReadUri();

    /**
     * Gets the partner base URI for writing data.
     */
    public function getWriteUri();
}
