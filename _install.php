<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Packager, a plugin for Dotclear 2.
#
# Copyright (c) 2006-2009 Pep and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) exit;

$package_version = $core->plugins->moduleInfo('packager','version');
$installed_version = $core->getVersion('packager');

if (version_compare($installed_version,$package_version,'>=')) {
	return;
}

try {
	// Default settings
	$core->blog->settings->setNameSpace('packager');
	
	$core->blog->settings->setNameSpace('system');
	$core->setVersion('packager',$package_version);
	unset($package_version,$installed_version);
	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
	unset($package_version,$installed_version);
	return false;
}
?>