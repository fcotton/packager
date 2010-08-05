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
if (!defined('DC_CONTEXT_ADMIN')) exit;

$_menu['Plugins']->addItem('Packager','plugin.php?p=packager','index.php?pf=packager/icon.png',
		preg_match('/plugin.php\?p=packager(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->isSuperAdmin());

if ($core->blog->settings->packager_tab_in_plugins) {
	$core->addBehavior('pluginsToolsTabs',array('packagerBehaviors','displayPluginsPanel'));
}

class packagerBehaviors
{
	public static function displayPluginsPanel(&$core)
	{
		echo '<div class="multi-part" id="packager" title="'. __('Pack plugins').'">';
		if (!empty($_REQUEST['packager_msg'])) {
			echo
				'<p class="message">'.
				html::escapeHTML($_REQUEST['packager_msg']).
				'</p>';
		}	
		
		uiPackager::displayTabContent($core->plugins->getModules(),'plugins','plugins.php');
		echo '</div>';
	}	
}
?>