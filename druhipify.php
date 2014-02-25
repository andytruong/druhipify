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
 *
 * Usage:
 *
 *  In your Drupal settings.php, include these lines:
 *
 *    define('GO_HIPCHAT_API_TOKEN', '***');
 *    define('GO_HIPCHAT_ROOM', 'p.Crom');
 *    require_once dirname(__FILE__) . '/druhipify.php';
 */

if (!function_exists('drush_go_hipchat')) {
  /**
   * Callback function to send a message to Hipchat room.
   *
   * @param  string $room_id
   * @param  string $msg
   */
  function drush_go_hipchat($room_id, $msg) {
    // #####################
    // Get params
    // #####################
    $token = drush_get_option('token', '');
    $from = drush_get_option('from', 'Druhipify');
    $color = drush_get_option('color', 'purple');
    $notify = drush_get_option('notify', 0);

    if (!$token) {
      if (!defined('GO_HIPCHAT_API_TOKEN')) {
        drush_set_error('No API Token specified.', 'Please run the command with --token value, or define GO_HIPCHAT_API_TOKEN constant in your settings.php');
      }
      $token = GO_HIPCHAT_API_TOKEN;
    }

    go_drush_hipchat($token, $room_id, $from, $msg, $color, $notify);
  }

  /**
   * Function to send hipchat message.
   */
  function go_drush_hipchat($token, $room_id, $from, $msg = '', $color = 'yellow', $notify = 0) {
    $ch = curl_init("https://api.hipchat.com/v1/rooms/message");
    curl_setopt_array($ch, array(
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_SSL_VERIFYPEER => FALSE,
      CURLOPT_NOSIGNAL => 1, // Suggested by Sang Le
      CURLOPT_POSTFIELDS => array(
        'auth_token'  => $token,
        'room_id'     => $room_id,
        'from'        => $from,
        'color'       => $color,
        'message'     => go_drush_hipchat_format_message($msg),
        'notify'      => $notify,
      ),
    ));

    $output = curl_exec($ch);
    if ($error = curl_error($ch)) {
      throw new Exception($error);
    }
  }

  /**
   * Format the message before sending…
   *
   * @param  string $msg
   * @return string
   */
  function go_drush_hipchat_format_message($msg) {
    $filter = new stdClass();
    $filter->settings['filter_url_length'] = 25;
    $msg = strip_tags($msg);
    function_exists('_filter_url')  && $msg = _filter_url($msg, $filter);
    function_exists('_filter_autop') && $msg = _filter_autop($msg);
    return $msg;
  }
}

if (function_exists('drush_get_option')) {
  register_shutdown_function(function() {
    exec('whoami; hostname -a', $output);
    $user = array_shift($output);
    $host = array_shift($output); // Suggested by Sang Le
    $pwd  = drush_get_context('DRUSH_DRUPAL_ROOT');
    $site_root = drush_get_context('DRUSH_DRUPAL_SITE_ROOT');

    $cmd = array();
    foreach ($_SERVER['argv'] as $a) {
      if (FALSE === strpos($a, '--php')) {
        $cmd[] = $a;
      }
    }

    if (FALSE !== strpos($cmd[0], 'drush')) {
      $cmd[0] = 'drush';
    }
    // Only log message from full bootstrap
    if (!drush_get_context('DRUSH_QUIET') && !drush_get_context('DRUSH_BACKEND')) {
      // Allow using default room
      $room = defined(GO_HIPCHAT_ROOM) ? GO_HIPCHAT_ROOM : GO_MONITOR_HIPCHAT_ROOM;
      drush_go_hipchat(
        $room,
        "<strong>[<code>{$user}@{$host}:{$pwd}/{$site_root}</code>]</strong>: <code>". implode(' ', $cmd) ."</code>"
      );
    }
  });
}
