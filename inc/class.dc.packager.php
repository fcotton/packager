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
class dcPackager
{
	protected static function scanDir($archiver,$dirname)
	{
		$exclude_list = array('.','..','.svn','CVS','.DS_Store','Thumbs.db');
		
		$dirname = preg_replace('|/$|','',$dirname);		
		if (!is_dir($dirname)) {
			throw new Exception(__('Invalid directory'));
		}
		
		$archiver->addEmptyDir($dirname.'/');
		$d = dir($dirname);
		while($entry = $d->read())
		{
			if (!in_array($entry,$exclude_list)) {
				if (is_dir($dirname.'/'.$entry)) {
					self::scanDir($archiver,$dirname.'/'.$entry);
				}
				else {
					$archiver->addFile($dirname.'/'.$entry);
				}
			}
		}
		$d->close();
	}
	
	public static function quickArchive($archive, $src_rep)
	{
		if (class_exists('ZipArchive')) {
			$archiver = new ZipArchive();
		} else {
			$archiver = new zipBuilder();
		}

		$archiver->open($archive,8);
		$cwd = getcwd();
		chdir(dirname($src_rep));
		self::scanDir($archiver,basename($src_rep));
		chdir($cwd);
		$archiver->close();
	}
	
	public static function pack($module,$prefix)
	{
		global $core;
		
		if (!$core->blog->settings->packager_repository) {
			$public = $core->blog->public_path;
		} else {
			$public = $core->blog->settings->packager_repository;			
		}

		try
		{
			if (empty($module['id'])) {
				$module['id'] = basename($module['root']);
			}
			
			$target = $public.'/'.$prefix.$module['id'];
			if (!empty($module['version'])) {
				$target .= '-'.$module['version'];
			}
			
			self::quickArchive($target.'.zip',$module['root']);
		}
		catch (Exception $e)
		{
			throw new Exception(__('Unable to build package. Error : ').$e->getMessage());
		}
	}
}
?>