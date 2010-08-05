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

if (!$core->blog->settings->packager_repository) {
	try 
	{
		// Default settings
		$core->blog->settings->setNameSpace('packager');
		$core->blog->settings->put('packager_repository',$core->blog->public_path);
		$core->blog->settings->put('packager_tab_in_plugins', false, 'boolean');
		http::redirect($p_url);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

$default_tab = '';

$repository     = $core->blog->settings->packager_repository;
$tab_in_plugins = $core->blog->settings->packager_tab_in_plugins;

if (empty($core->themes)) {
	$core->themes = new dcModules($core);
	$core->themes->loadModules($core->blog->themes_path,null);
}

if (!empty($_POST['do_package']) && is_array($_POST['pack'])) {
	if (!empty($_POST['pack'])) {	
		$type = ($_POST['addons_type'] == 'plugins')?'plugins':'themes';
		$default_tab = 'packager_'.$type;
		$prefix = substr($type,0,-1).'-';
		
		// Build package(s)
		try
		{
			foreach (array_keys($_POST['pack']) as $ext_id) {
				if (!$core->{$type}->moduleExists($ext_id)) {
					throw new Exception(__('No such '.substr($type,0,-1).' ('.$ext_id.').'));
				}
				$ext = $core->{$type}->getModules($ext_id);
				$ext['id'] = $ext_id;
	
				# --BEHAVIOR-- packagerBeforeCreate
				$core->callBehavior('packagerBeforeCreate', $type, $ext);
				
				dcPackager::pack($ext,$prefix);
				
				# --BEHAVIOR-- packagerAfterCreate
				$core->callBehavior('packagerAfterCreate', $type, $ext);
			}

			$msg = __('Package(s) successfully created.');
			
			if (!empty($_POST['redir'])) {
				$redir = $_POST['redir'];
				if (preg_match('!^plugins.php$!',$redir)) {
					$redir .=	'?'.http_build_query(array('tab' => 'packager', 'packager_msg' => $msg),'','&');
				}
				http::redirect($redir);
			}
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
}
elseif (!empty($_POST['saveconfig']))
{
	$repository = trim(html::escapeHTML($_POST['repository']));
	$tab_in_plugins = (empty($_POST['tab_in_plugins']))?false:true;
	if (empty($repository) || !is_writeable($repository)) {
		$repository = $core->blog->public_path;
	}

	try
	{
		$core->blog->settings->setNameSpace('packager');
		$core->blog->settings->put('packager_repository',$repository);
		$core->blog->settings->put('packager_tab_in_plugins',$tab_in_plugins,'boolean');
		
		$default_tab = 'packager_options';
		$msg = __('Configuration successfully updated.');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
?>
<html>
<head>
	<title><?php echo __('Packager'); ?></title>
	<?php echo dcPage::jsPageTabs($default_tab); ?>
</head>

<body>
<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('Packager'); ?></h2>

<?php if (!empty($msg)) echo '<p class="message">'.$msg.'</p>'; ?>

<div class="multi-part" id="packager_plugins" title="<?php echo __('Pack plugins'); ?>">
	<?php dcPackager::displayTabContent($core->plugins->getModules(),'plugins'); ?>
</div>

<div class="multi-part" id="packager_themes" title="<?php echo __('Pack themes'); ?>">
	<?php dcPackager::displayTabContent($core->themes->getModules(),'themes'); ?>
</div>

<div class="multi-part" id="packager_options" title="<?php echo __('Options'); ?>">
	<form method="post" action="plugin.php">
		<fieldset>
			<legend><?php echo __('General options'); ?></legend>
			<p>
				<label class=" classic"><?php echo __('Repository path :').' '; ?>
				<?php echo form::field('repository', 40, 255, $repository); ?>
				</label>
			</p>
		</fieldset>
		<fieldset>
			<legend><?php echo __('Advanced options'); ?></legend>
			<p>
				<label class=" classic">
				<?php echo form::checkbox('tab_in_plugins',1,$tab_in_plugins); ?>
				<?php echo ' '.__('Add packager tab in plugins management'); ?>
				</label>
			</p>
		</fieldset>
		<p>
			<input type="hidden" name="p" value="packager" />
			<?php echo $core->formNonce(); ?>
			<input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" />
		</p>
	</form>
</div>

</body>
</html>