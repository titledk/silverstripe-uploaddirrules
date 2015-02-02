<?php
/**
 * Form field for editing an object's related folder
 *
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2015, Title Web Solutions
 */
class AssetsFolderURLSegmentField extends SiteTreeURLSegmentField {


	protected $helpText = 'Note that if you change this directory, you might need to update links to any uploaded images in the content area.';
	//protected $urlPrefix = '';
	protected $urlSuffix = '';
	
	protected $assetsFolderID = 0;




	//public function getURL() {
	//	return 'test';
	//	return $this->Value();
	//}
	
	public function setAssetsFolderID($id) {
		$this->assetsFolderID = $id;
	}
	
	
	public function getFolderAdminUrl() {
		return "/admin/assets/show/{$this->assetsFolderID}";
	}
	
}