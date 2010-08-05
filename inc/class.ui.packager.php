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
class uiPackager
{
	public static function displayTabContent($elements, $type = 'plugins',$redir = '')
	{
		global $core;
		
		$type  = ($type == 'plugins')?'plugins':'themes';
		$title = __(ucfirst(substr($type,0,-1)));
		
		if (!empty($elements) && is_array($elements)) 
		{
			echo
			'<form action="plugin.php" method="post">'.
			'<table class="clear"><tr>'.
			'<th colspan="2">'.$title.'</th>'.
			'<th class="nowrap">'.__('Version').'</th>'.
			'<th class="nowrap">'.__('Description').'</th>'.
			'</tr>';
		
			foreach ($elements as $k => $v)
			{	
				echo
				'<tr class="line">'.
				'<td>'.form::checkbox(array('pack['.html::escapeHTML($k).']'),1).'</td>'.
				'<td class="minimal nowrap">'.html::escapeHTML($v['name']).'</td>'.
				'<td class="minimal">'.html::escapeHTML($v['version']).'</td>'.
				'<td class="maximal">'.html::escapeHTML($v['desc']).'</td>'.
				'</tr>';
			}
			echo
			'</table>'.
			'<p><input type="hidden" name="p" value="packager" />'.
			'<input type="hidden" name="addons_type" value="'.$type.'" />';
			
			if (!empty($redir)) {
				echo
				'<input type="hidden" name="redir" value="'.html::escapeHTML($redir).'" />';
			}
			
			echo
			'<input type="submit" name="do_package" value="'.__('Pack selected '.$type).'" />'.
			$core->formNonce().'</p>'.
			'</form>';
		}
		else
		{
			echo '<p><strong>'.__('No available '.$type).'</strong></p>';
		}		
	}
}
?>