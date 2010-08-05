<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Packager, a plugin for Dotclear 2.
#
# Copyright (c) 2006-2010 Pep and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) return;

$package_version = $core->plugins->moduleInfo('packager','version');
$installed_version = $core->getVersion('packager');

if (version_compare($installed_version,$package_version,'>=')) {
	return;
}

try {
	$core->blog->settings->addNameSpace('packager');
	$core->blog->settings->packager->put('packager_repository','', 'string', '', false, true);
	$core->blog->settings->packager->put('packager_repository_plugins','', 'string', '', false, true);
	$core->blog->settings->packager->put('packager_repository_themes','', 'string', '', false, true);
	$core->blog->settings->packager->put('packager_tab_in_plugins', false, 'boolean', '', false, true);
	$core->setVersion('packager',$package_version);
	return true;
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
	return false;
}
?>