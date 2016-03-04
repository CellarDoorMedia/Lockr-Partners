<?php

/**
 * @file
 * Hooks and callbacks for drush.
 */

/**
 * Implements hook_drush_command().
 */
function lockr_pantheon_drush_command() {
  $info['lockdown'] = array(
    'description' => 'Patch modules to work with key.',
    'aliases' => array('ld'),
    'core' => array('7'),
    'scope' => 'site',
  );

  return $info;
}

function drush_lockr_pantheon_lockdown() {
  $raw_path = 'https://raw.githubusercontent.com/CellarDoorMedia/Lockr-Patches/master';

  $reg_file = "{$raw_path}/registry.json";
  drush_log("Downloading registry file: {$reg_file}.");
  $registry = file_get_contents($reg_file);
  $registry = json_decode($registry, TRUE);

  if (json_last_error() !== JSON_ERROR_NONE) {
    drush_log(
      'There was an error downloading the patch registry.',
      LogLevel::ERROR
    );
    return;
  }

  $names = implode(', ', array_keys($registry['drupal7']));
  drush_log("Patches available for: {$names}.");
  foreach ($registry['drupal7'] as $name => $path) {
    // We use `drupal_get_path()' and check its result instead of
    // `module_exists()' because it will find disabled modules.
    $module_path = drupal_get_path('module', $name);
    if (!$module_path) {
      drush_log("Module not found: {$name}.");
      continue;
    }

    $module_path = DRUPAL_ROOT . '/' . $module_path;
    if (!is_dir($module_path)) {
      drush_log("Module path does not exist: {$module_path}");
      continue;
    }

    // The lockfile prevents double-patching a module if lockdown is
    // called more than once. Applying a patch more than once can be
    // disasterous, and we don't want that.
    $lockfile = "{$module_path}/.lockr-patched";
    if (is_file($lockfile)) {
      drush_log("{$name} already patched.");
      drush_log("Remove {$lockfile} to patch again.");
      drush_log("Do so at your own peril.");
      continue;
    }

    $patch_path = "{$module_path}/key-integration.patch";
    $patch_remote = "{$raw_path}/{$path}";
    drush_log("Downloading {$patch_remote}.");
    copy($patch_remote, $patch_path);

    drush_log("Patching {$name}.");
    $cmd = implode(' ', array(
      'patch',
      // We do not need a backup because reverting the patch can be done
      // via the user's version control system.
      '--no-backup-if-mismatch',
      '-N',
      '-p1',
      '-d', escapeshellarg($module_path),
      '<',  escapeshellarg($patch_path),
    ));
    drush_log("Running `{$cmd}`.");
    ob_start();
    passthru($cmd, $return_code);
    drush_log(ob_get_clean());

    if ($return_code === 0) {
      // Patch is OK, go ahead and write the lockfile and remove the
      // downloaded patch.
      drush_log("Patch successful, writing lockfile.");
      file_put_contents($lockfile, '');
      unlink($patch_path);
    }
    else {
      drush_log("Failed to patch {$name}.", 'error');
      drush_log("Patch file left at '{$patch_path}'.", 'error');
    }
  }
}

