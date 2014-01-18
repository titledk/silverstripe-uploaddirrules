<?php
/**
 * This extension can be added to any object that should have a relationship
 * to a folder inside /assets
 * 
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2013, Title Web Solutions
 */
class AssetsFolderExtension extends DataExtension {

	static $has_one = array(
		'AssetsFolder' => 'Folder' 
	);	
	
	/**
	 * First run: Find or make
	 */
	public function FindOrMakeAssetsFolder($url, $doWrite = true){
		$owner = $this->owner;
		$dir = Folder::find_or_make($url);
		$owner->AssetsFolderID = $dir->ID;
		if ($doWrite) {
			$owner->write();
		}
		return $dir;
	}
	
}