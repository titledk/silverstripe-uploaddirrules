<?php
/**
 * Helper for consistent upload directories for sites using the subsites module
 *
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2015, Title Web Solutions
 */

class SubsiteUploadDirRules extends UploadDirRules {


	/**
	 * Base rules for subsite directory
	 * TODO make it configurable, allowing for subsite dirs to be either in the root, or inside of a "subsites" directory
	 * 
	 * @return bool|string
	 */
	public static function calc_directory_for_subsite(){
		$subsite = Subsite::currentSubsite();
		if ($subsite) {
			$title = $subsite->Title;
			$url = strtolower(singleton('SiteTree')->generateURLSegment($title));
			return $url;
		} else {
			return false;
		}

	}

	/**
	 * Getting subsite directory based on it's assets folder
	 * 
	 * @return bool|mixed
	 */
	public static function get_directory_for_subsite(){
		$subsite = Subsite::currentSubsite();
		if ($subsite) {
			if ((int) $subsite->AssetsFolderID > 0) {
				$dirObj = $subsite->AssetsFolder();
				
				$dirName = str_replace('assets/', '', $dirObj->Filename);
				
				//make sure we've got no trailing slashes
				$dirName = str_replace('/', '', $dirName);
				return $dirName;
			}
		} else {
			return false;
		}
	}


	/**
	 * Full subsite directory
	 * 
	 * @param DataObject $do
	 * @return bool|string
	 */
	public static function calc_full_directory_for_object(DataObject $do){
		
		if ($do->ClassName == 'Subsite') {
			//If we're dealing with an actual subsite, we only want the subsite part
			return self::calc_directory_for_subsite();
		} else {
			//If we're dealing with a path inside of a subsites,
			//we need at least be sure that the subsite is having an asset directory

			$subsite_dir = self::get_directory_for_subsite();
			if ($subsite_dir) {
				$str = parent::calc_full_directory_for_object($do);
				$str = "$subsite_dir/$str";
				return $str;
			} else {
				return false;
			}
		}
	}

}
