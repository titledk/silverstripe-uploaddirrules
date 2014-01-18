<?php
/**
 * Helper for consistent upload directories
 * 
 * These rules assure that files are being uploaded to a subdirectory of the "assets" directory.
 * Additionally, files are saved in directories, corresponding to the page that is currently being edited.
 * 
 * In order for this to work out-of the box, the following extensions need to be configured:
 * 
 * SiteTree:
 *   extensions:
 *     - AssetsFolderExtension
 *		 - UploadDirRules_SiteTreeExtension
 * LeftAndMain:
 *   extensions:
 *    - UploadDirRules_LeftAndMainExtension 
 * 
 * 
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2014, Title Web Solutions
 */
class UploadDirRules {

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
			
			$msg = null;
			if($this->owner instanceof UploadDirRulesInterface) {
				$msg = $this->owner->getMessageSaveFirst();
			} else {
				//default message
				$msg = 'Please <strong>choose a name and save</strong> for adding content.';
			}			
			if ($msg) {
				$htmlField = new LiteralField('ContentPlaceholder', '
					<p class="message notice" >' . $msg . '</p>
				');
				$fields->addFieldToTab('Root.Main', $htmlField, 'Title');
			}
		} else {
			$dirName = UploadDirRules::current_page_directory($this->owner);
			Upload::config()->uploads_folder = $dirName;

			$msg = null;
			if($this->owner instanceof UploadDirRulesInterface) {
				$msg = $this->owner->getMessageUploadDirectory();
			} else {
				//default message
				$msg = '<em>Files uploaded via the content area will be uploaded to</em><br /> <strong>' .Upload::config()->uploads_folder . '</strong>';
			}			
			if ($msg) {
				$fields->addFieldToTab('Root.Main', 
					new LiteralField('UploadDirectorNote', '
					<div class="field text" id="UploadDirectorNote">
						<label class="left">Upload Directory</label>
						<div class="middleColumn">
							<p style="margin-bottom: 0; padding-top: 0px;">
								' . $msg . '
							</p>
						</div>
					</div>				

					'),
				'Content');
			}
			
//					<p class="message notice" >
//					Note: Files uploaded via the content area will be uploaded to <strong>' .Upload::config()->uploads_folder . '</strong>
//					</p>
			
		}
		
		
    return $fields;		
	}		

	/**
	 * Creation and association of assets folder,
	 * if the page name is other than the standard page name
	 */
	function onBeforeWrite() {
		parent::onBeforeWrite();
		if ($this->owner->AssetsFolderID == 0) {
			$className = $this->owner->ClassName;
			$translatedClassName = singleton($className)->i18n_singular_name();
			//BTW: this will probably only work in English admin anyway
			if ($this->owner->Title != "New $translatedClassName") {
				
				$url = null;
				//check if the page we're having is implementing the UploadDirRulesInterface
				//for rule customization
				if($this->owner instanceof UploadDirRulesInterface) {
					$url = $this->owner->getCalcAssetsFolderDirectory();
				} else {
					//else use the default settings
					$pageUrlPart = UploadDirRules::page_directory_part($this->owner);
					$url = $pageUrlPart;
				}
				
				if ($url) {
					//this creates the directory, and attaches it to the page
					//- without saving it (see: false) - as this is called on "onBeforeWrite",
					//and we're expecting the saving to be taking place just after this is called
					$dirObj = $this->owner->FindOrMakeAssetsFolder($url, false);
				}
				
			}
		}
		
	}
	
	public function getAssetsFolderDir() {
		if ($this->owner->getField('AssetsFolderID') != 0) {
			$dirObj = $this->owner->AssetsFolder();
			$dirName = str_replace('assets/', '', $dirObj->Filename);
			return $dirName;
		}
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