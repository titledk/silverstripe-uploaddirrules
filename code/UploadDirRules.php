<?php
/**
 * Helper for consistent upload directories
 * 
 * These rules assure that files are being uploaded to a subdirectory of
 * the "assets" directory.
 * Additionally, files are saved in directories, corresponding to the page
 * that is currently being edited.
 * 
 * In order for this to work out-of the box, the following extensions need to be
 * configured:
 *
 *
 * For Pages:
 * 
 * SiteTree:
 *   extensions:
 *     - AssetsFolderExtension
 *     - UploadDirRules_SiteTreeExtension
 * LeftAndMain:
 *   extensions:
 *    - UploadDirRules_LeftAndMainExtension 
 *
 *
 * For DataObjects
 *
 * static $extensions = array(
 * 	'AssetsFolderExtension',
 * 	'UploadDirRules_DataObjectExtension'
 * );
 *
 * 
 * 
 * 
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2014, Title Web Solutions
 */
class UploadDirRules extends Object {

	//set true for testing
	private static $dryrun = false;
	
	
	/**
	 * This method returns a page's directory name, 
	 * if that page has a directory associated
	 * Can be called with a page object or without - if called without, the we use the page the user 
	 * is currently on in the CMS
	 * 
	 */
	public static function current_page_directory($page = null){
		if (!$page) {
			$curr = LeftAndMain::curr();
			$page = $curr->currentPage();
			//echo "test" . $curr->currentPageID();
			//echo "test" . $page->ID;
		}
		
		
		if ($page && $page->getField('AssetsFolderID') != 0) {
			$dirObj = $page->AssetsFolder();
			$dirName = str_replace('assets/', '', $dirObj->Filename);
			return $dirName;
			//TODO: Replace with:
			//return $page->getAssetsFolderDir();
		}

	}
	
	/**
	 * Definition of the page directory part
	 * @param Page $page
	 * @return string
	 */
	public static function page_directory_part($page){
		if ($page) {
			$pageUrl = $page->URLSegment;
			
			//Setting the dir name
			//The assumption is that prepending is better than appending because
			//page urls may change, and that way directories can always easily be identified by the page ID
			
			$dirName = $page->ID . '-' . $pageUrl;
			//echo $dirName;
			
			return $dirName;
		}
	}
	
	

//	/**
//	 * Setting the global uploads directory
//	 * This is done so files uploaded e.g. in the {@see HTMLEditorField} are saved 
//	 * in appropriate directories
//	 */
//	public static function set_global_uploads_directory(){
//		$uploadDir = self::current_page_directory();
//		
//		//echo $uploadDir;
//		
//		if ($uploadDir) {
//			//See http://doc.silverstripe.com/framework/en/topics/configuration
//			Upload::config()->uploads_folder = $uploadDir;
//			
//		}
//	}	
	
	

}


/**
 * This is for dataobjects
 */
class UploadDirRules_DataObjectExtension extends DataExtension {


	function updateCMSFields(FieldList $fields) {

		//cms fields can be disabled via config
		$noCmsFields = false;
		if (UploadDirRules::config()->noCmsFields) {
			$noCmsFields = true;
		}
		
		
		$fields->removeByName('UploadDirRulesNote');

		//Don't allow any content creation as long as we don't have an associated
		//assets directory
		if ($this->owner->AssetsFolderID == 0) {
			if (!$noCmsFields) {
				$htmlField = $this->owner->cmsFieldsMessage(false);
				$fields->addFieldToTab('Root.Main', $htmlField);
			}
		} else {
			$dirName = $this->owner->getAssetsFolderDir();
			Upload::config()->uploads_folder = $dirName;

			if (!$noCmsFields) {
				$htmlField = $this->owner->cmsFieldsMessage(true);
				$fields->addFieldToTab('Root.Main', $htmlField);
			}
		}
		
    return $fields;
	}	




}




/**
 * This is for pages
 * 
 * This extension is added to {@see SiteTree} to make pages aware of the
 * Upload dir rules, force the user to choose a name before adding content,
 * and using that name to create an assets directory
 */
class UploadDirRules_SiteTreeExtension extends DataExtension {

	function updateCMSFields(FieldList $fields) {

		//Don't allow any content creation as long as we don't have an associated
		//assets directory
		if ($this->owner->AssetsFolderID == 0) {
			$fields->replaceField('Content', new HiddenField('Content'));
			$fields->removeByName('Metadata');

			$htmlField = $this->owner->cmsFieldsMessage(false);
			$fields->addFieldToTab('Root.Main', $htmlField, 'Title');

		} else {
			$dirName = UploadDirRules::current_page_directory($this->owner);
			Upload::config()->uploads_folder = $dirName;

			$htmlField = $this->owner->cmsFieldsMessage(true);

			$fields->addFieldToTab('Root.Main', $htmlField, 'Content');

		}

		return $fields;
	}

}


/**
 * Extension for {@see LeftAndMainExtension} for ensuring consistent file uploading
 * when working with subsites
 */
class UploadDirRules_LeftAndMainExtension extends LeftAndMainExtension {

	public function init() {
		//setting the uploads directory to make sure all images are saved
		//according to the rules set in {@see UploadDirRules}
		
		$owner = $this->owner;
		
		$link = $owner->Link();
		//echo $link;
		
		if (
			$link == 'admin/pages/' ||
			$link == 'admin/pages/edit/'
		) {
			
			$dirName = UploadDirRules::current_page_directory();
			if ($dirName) {
				Upload::config()->uploads_folder = $dirName;	
			}
			
		}
		
	}
}