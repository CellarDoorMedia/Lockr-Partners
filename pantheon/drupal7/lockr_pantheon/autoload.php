<?php

/**
 * @file
 * Lockr autoloader.
 */

function lockr_pantheon_autoload($class) {
  if (substr($class, 0, 6) !== 'Lockr\\') {
    return FALSE;
  }
  $parts = explode('\\', $class);
  array_shift($parts);
  $path = array('src');
  foreach ($parts as $part) {
    $path[] = $part;
  }
  require_once __DIR__ . '/' . implode('/', $path) . '.php';
  return TRUE;
}

spl_autoload_register('lockr_pantheon_autoload');
