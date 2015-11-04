<?php

/**
 * @file
 * Lockr autoloader.
 */

function lockr_pantheon_autoload($class) {
  if (substr($class, 0, 6) !== 'Lockr\\') {
    return FALSE;
  }
  $file = __DIR__.'/src/'.str_replace('\\', '/', $class).'.php';
  if (file_exists($file)) {
    include_once $file;
    return true;
  }
  return false;
}

spl_autoload_register('lockr_pantheon_autoload');
