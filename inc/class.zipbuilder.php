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
class zipBuilder
{
	protected $ctrl_dir;
	protected $datasec;
	protected $eof_ctrl_dir; 
	protected $old_offset;

	protected $target_name;

	public function __construct()
	{
		$this->ctrl_dir	= array();
		$this->datasec		= array();
		$this->old_offset	= 0;
		$this->eof_ctrl_dir	= "\x50\x4b\x05\x06\x00\x00\x00\x00";
	}
	
	public function open($filename)
	{
		// Ajouter quelques vérifications d'usage par ici ...
		$this->target_name = $filename;
	}

	public function addEmptyDir($name)
	{
		$name = str_replace("\\", "/", $name);

		$fr =
			"\x50\x4b\x03\x04".
			"\x0a\x00".		// ver needed to extract
			"\x00\x00".		// gen purpose bit flag
			"\x00\x00".		// compression method
			"\x00\x00\x00\x00";	// last mod time and date

		$fr .= 
			pack("V",0).			// crc32
			pack("V",0).			// compressed filesize
			pack("V",0).			// uncompressed filesize
			pack("v",strlen($name)). // length of pathname
			pack("v",0). 			// extra field length
			$name;				// end of "local file header" segment

		// "data descriptor" segment (optional but necessary if archive is not served as file)
		$fr .= 
			pack("V",(isset($crc))?$crc:0).	 	//crc32
			pack("V",(isset($c_len))?$c_len:0).	//compressed filesize
			pack("V",(isset($unc_len))?$unc_len:0);	//uncompressed filesize

		// add this entry to array
		$this->datasec[] = $fr;
		$new_offset = strlen(implode("",$this->datasec));

		// now add to central record
		$cdrec =
			"\x50\x4b\x01\x02".
			"\x00\x00".			// version made by	
			"\x0a\x00".			// version needed to extract
			"\x00\x00".			// gen purpose bit flag
			"\x00\x00".			// compression method
			"\x00\x00\x00\x00".		// last mod time & date
			pack("V",0).			// crc32
			pack("V",0).			// compressed filesize
			pack("V",0).			// uncompressed filesize
			pack("v",strlen($name)). // length of filename
			pack("v",0).			// extra field length
			pack("v",0).			// file comment length
			pack("v",0).			// disk number start
			pack("v",0);			// internal file attributes
	
		/*
		$ext = "\x00\x00\x10\x00";
		$ext = "\xff\xff\xff\xff";
		*/

		$cdrec .=
			pack("V",16).				// external file attributes  - 'directory' bit set
			pack("V",$this->old_offset).	// relative offset of local header
			$name;

		$this->old_offset = $new_offset;
		$this->ctrl_dir[] = $cdrec;
	}

	public function addFile($name)
	{
		if (!file_exists($name)) {
			throw new Exception(__('File does not exist'));
		}
		if (!is_readable($name)) {
			throw new Exception(__('Cannot read file'));
		}
		$this->addFromString($name,file_get_contents($name));
	}

	public function addFromString($name,$data)
	{
		$name = str_replace("\\", "/", $name);
		
		$fr =
			"\x50\x4b\x03\x04".
			"\x14\x00".		// ver needed to extract
			"\x00\x00".		// gen purpose bit flag
			"\x08\x00".		// compression method
			"\x00\x00\x00\x00";	// last mod time and date
		
		$unc_len	= strlen($data);
		$crc		= crc32($data);
		$zdata	= gzcompress($data);
		$zdata	= substr(substr($zdata,0,strlen($zdata) - 4),2); // fix crc bug
		$c_len	= strlen($zdata);
		
		$fr .=
			pack("V",$crc).		// crc32
			pack("V",$c_len).		//compressed filesize
			pack("V",$unc_len).		//uncompressed filesize
			pack("v",strlen($name)).	//length of filename
			pack("v",0).			//extra field length
			$name;				// end of "local file header" segment
			
		$fr .=
			$zdata;				// "file data" segment
			
		$fr .=
			pack("V",$crc).		//crc32
			pack("V",$c_len).		//compressed filesize
			pack("V",$unc_len);		//uncompressed filesize
		
		// add this entry to array
		$this->datasec[] = $fr;
		$new_offset = strlen(implode("",$this->datasec));
		
		// now add to central directory record
		$cdrec =
			"\x50\x4b\x01\x02".
			"\x00\x00".				// version made by
			"\x14\x00".				// version needed to extract
			"\x00\x00".				// gen purpose bit flag
			"\x08\x00".				// compression method
			"\x00\x00\x00\x00".			// last mod time & date
			pack("V",$crc).			// crc32
			pack("V",$c_len).			// compressed filesize
			pack("V",$unc_len).			// uncompressed filesize
			pack("v",strlen($name)).		// length of filename
			pack("v",0).				// extra field length
			pack("v",0).				// file comment length
			pack("v",0).				// disk number start
			pack("v",0).				// internal file attributes
			pack("V",32).				// external file attributes - 'archive' bit set
			pack("V",$this->old_offset ).	// relative offset of local header
			$name;

		$this->old_offset = $new_offset;
		$this->ctrl_dir[] = $cdrec;
	}

	protected function _getZipDatas()
	{
		$data = implode("",$this->datasec);
		$ctrldir = implode("",$this->ctrl_dir);

		return
			$data.
			$ctrldir.
			$this->eof_ctrl_dir.
			pack("v",sizeof($this->ctrl_dir)).	// total # of entries "on this disk"
			pack("v",sizeof($this->ctrl_dir)).	// total # of entries overall
			pack("V",strlen($ctrldir)).		// size of central dir
			pack("V",strlen($data)).			// offset to start of central dir
			"\x00\x00";					// .zip file comment length
    }
    
	public function close()
	{
		if (!$this->target_name) {
			throw new Exception(__('Unspecified archive name'));
		}
		if (file_put_contents($this->target_name,$this->_getZipDatas()) === false) {
			throw new Exception(__('Cannot write Zip archive'));
		}
    }
}
?>