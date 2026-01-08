<?php

 /**
  *
  * @package OpenEMR
  * @link    http://www.open-emr.org
  *
  * @author    Eric Gearman <egearman@ibss.net>
  * @copyright Copyright (c) 2025 Eric Gearman <egearman@ibss.net>
  * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
  */

namespace OpenEMR\Modules\X12AutoProcess;

/**
 * @global OpenEMR\Core\ModulesClassLoader $classLoader
 */

$classLoader->registerNamespaceIfNotExists('OpenEMR\\Modules\\X12AutoProcess\\', __DIR__ . DIRECTORY_SEPARATOR . 'src');
/**
 * @global EventDispatcherInterface $eventDispatcher Injected by the OpenEMR module loader;
 */

$bootstrap = new Bootstrap($eventDispatcher);
$bootstrap->subscribeToEvents();

