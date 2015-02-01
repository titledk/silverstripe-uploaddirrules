<?php
/**
 * This extension can be added to any object that should have a relationship
 * to a folder inside /assets
 * 
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2015, Title Web Solutions
 */
class AssetsFolderExtension extends DataExtension {

	static $has_one = array(
		'AssetsFolder' => 'Folder' 
	);


	/**
	 * Displaying assets folder relation in CMS fields
	 * as well as setting the global upload config
	 * 
	 * @param FieldList $fields
	 * @return FieldList|void
	 */
	function updateCMSFields(FieldList $fields) {
		$dirName = $this->owner->getAssetsFolderDirName();
		$dirExists = false;
		
		if ($dirName) {
			$dirExists = true;
			Upload::config()->uploads_folder = $dirName;
		}

		//Fields
		//TODO make it configurable if they should be shown
		//TODO make field placement configurable
		$htmlField = $this->cmsFieldsMessage($dirExists);
		$fields->addFieldToTab('Root.Main', $htmlField, 'Content');

		return $fields;
	}
	

	/**
	 * Creation and association of assets folder,
	 * once a data object has been created (and is ready for it)
	 */
	function onBeforeWrite() {
		parent::onBeforeWrite();

		//creation will only be considered if the object has an ID
		//and has no folder relation
		if (($this->owner->ID) > 0 && ($this->owner->AssetsFolderID) == 0) {

			//the default rules only require the object to have an ID
			//but more sophisticated rules might require more - e.g. a title to be set
			//thus we check if the object is ready for folder creation - if custom rules
			//(UploadDirRulesInterface) habe been set
			if($this->owner instanceof UploadDirRulesInterface) {
				if (! $this->owner->getReadyForFolderCreation()) {
					return false;
				}
			}

			$url = null;
			//check if the page we're having is implementing the UploadDirRulesInterface
			//for rule customization
			if($this->owner instanceof UploadDirRulesInterface) {
				$url = $this->owner->getCalcAssetsFolderDirectory();
			} else {
				//else use the default settings
				
				$class = UploadDirRules::get_rules_class();
				$url = $class::calc_full_directory_for_object($this->owner);
			}

			if ($url) {
				//this creates the directory, and attaches it to the page
				//- without saving it (see: false) - as this is called on "onBeforeWrite",
				//and we're expecting the saving to be taking place just after this is called
				$dirObj = $this->findOrMakeAssetsFolder($url, false);
			}
		}

	}
	
	
	/**
	 * Find or make assets folder
	 * called from onBeforeWrite
	 * 
	 * @param string  $url
	 * @param bool    $doWrite
	 * @return Folder|null
	 */
	protected function findOrMakeAssetsFolder($url, $doWrite = true){
		$owner = $this->owner;
		$dir = Folder::find_or_make($url);
		$owner->AssetsFolderID = $dir->ID;
		if ($doWrite) {
			$owner->write();
		}
		return $dir;
	}


	/**
	 * Name of the associated assets folder
	 * @return string|null
	 */
	public function getAssetsFolderDirName() {
		if ($this->owner->getField('AssetsFolderID') != 0) {
			$dirObj = $this->owner->AssetsFolder();
			$dirName = str_replace('assets/', '', $dirObj->Filename);
			return $dirName;
		}
	}


	/**
	 * Upload Dir Rules message to display in the CMS
	 * 
	 * @param bool $dirExists
	 * @return LiteralField|null
	 */
	protected function cmsFieldsMessage($dirExists = false){
		$field = null;
		$msg = null;
		if ($dirExists) {
			
			//Message
			$defaultMsg = '<em>Files uploaded via the content area will be uploaded to</em>' .
				'<br /> <strong>'  .Upload::config()->uploads_folder . '</strong>';
			if($this->owner instanceof UploadDirRulesInterface) {
				$msg = $this->owner->getMessageUploadDirectory();
			}
			if (!$msg) {
				$msg = $defaultMsg;
			}
			
			//Field
			$field = new LiteralField('UploadDirRulesNote', '
				<div class="field text" id="UploadDirRulesNote">
					<label class="left">Upload Directory</label>
					<div class="middleColumn">
						<p style="margin-bottom: 0; padding-top: 0px;">
							' . $msg . '
						</p>
					</div>
				</div>
				');
		} else {
			
			//Message
			$defaultMsg = 'Please <strong>choose a name and save</strong> for adding content.';
			if($this->owner instanceof UploadDirRulesInterface) {
				$msg = $this->owner->getMessageSaveFirst();
			}
			if (!$msg) {
				$msg = $defaultMsg;
			}
			$field = new LiteralField('UploadDirRulesNote', '
				<p class="message notice" >' . $msg . '</p>
			');
		}
		return $field;
	}

}
