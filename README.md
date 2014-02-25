druhipify
=========

Notification script for Drush to Hipchat, help us to monitor all Drush commands run on server


### Usage

#### Log for specific site:

In settings.php, add these lines:

```php
<?php
/**
 * @file settings.php
 */

define('GO_HIPCHAT_API_TOKEN', '***');
define('GO_HIPCHAT_ROOM', 'Room Name');
require_once '/path/to/drushipify.php';
```

#### Log for all sites:

Create this file:

```php
<?php
/**
 * @file ~/.drush/druhipify/druhipify.drush.inc
 */

define('GO_HIPCHAT_API_TOKEN', '***');
define('GO_MONITOR_HIPCHAT_ROOM', 'Room Name');
require_once '/path/to/drushipify.php';

// In any client site, just define GO_HIPCHAT_ROOM, notification will post to that room
```
