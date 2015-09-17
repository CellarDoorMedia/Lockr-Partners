<?php

/**
 * @file
 * Contains \Lockr\Exceptions\NetworkException.
 */

namespace Lockr\Exceptions;

/**
 * Exception thrown when there is some kind of network error.
 *
 * Lockr does not need to differentiate between client or server error,
 * both current result in a call failure.
 */
class NetworkException extends \Exception {
}
