<?php

/**
 * @file druhipify.php
 *
 *  https://github.com/andytruong/druhipify
 *
 * Copyright (c) 2014 Andy Truong <thehongtt@gmail.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms are permitted
 * provided that the above copyright notice and this paragraph are
 * duplicated in all such forms and that any documentation,
 * advertising materials, and other materials related to such
 * distribution and use acknowledge that the software was developed
 * by the <organization>.  The name of the
 * <organization> may not be used to endorse or promote products derived
 * from this software without specific prior wr_filter_urlitten permission.
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND WITHOUT ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, WITHOUT LIMITATION, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.
 */

if (function_exists('drush_get_option')) {
  if (!function_exists('drush_go_hipchat')) {
    require_once dirname(__FILE__) . '/druhipify.hipchat.php';
  }

  register_shutdown_function(function() {
    exec('whoami; hostname -a', $output);
    $user = array_shift($output);
    $host = array_shift($output); // Suggested by Sang Le
    $pwd  = drush_get_context('DRUSH_DRUPAL_ROOT');
    $site_root = drush_get_context('DRUSH_DRUPAL_SITE_ROOT');

    $cmd = array();

    // Do not log php options
    foreach ($_SERVER['argv'] as $a) {
      if (FALSE === strpos($a, '--php')) {
        $cmd[] = $a;
      }
    }

    // Use `drush` instead of `/full/path/to/drush.php`
    if (FALSE !== strpos($cmd[0], 'drush')) {
      $cmd[0] = 'drush';
    }

    // Only log message from full bootstrap
    if (!drush_get_context('DRUSH_QUIET') && !drush_get_context('DRUSH_BACKEND')) {
      // Allow using default room
      $room = defined(GO_HIPCHAT_ROOM) ? GO_HIPCHAT_ROOM : GO_MONITOR_HIPCHAT_ROOM;

      $msg  = "<strong>[<code>{$user}@{$host}:{$pwd}/{$site_root}</code>]</strong>:";
      $msg .= " <code>". implode(' ', $cmd) ."</code>";

      drush_go_hipchat($room, $msg);
    }
  });
}
