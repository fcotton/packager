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
if (!defined('DC_RC_PATH')) return;

$GLOBALS['__autoload']['dcPackager'] = dirname(__FILE__).'/inc/class.dc.packager.php';
$GLOBALS['__autoload']['uiPackager'] = dirname(__FILE__).'/inc/class.ui.packager.php';
$GLOBALS['__autoload']['zipBuilder'] = dirname(__FILE__).'/inc/class.zipbuilder.php';
?>