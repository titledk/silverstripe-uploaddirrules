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


	public function getAssetsFolderDir() {
		if ($this->owner->getField('AssetsFolderID') != 0) {
			$dirObj = $this->owner->AssetsFolder();
			$dirName = str_replace('assets/', '', $dirObj->Filename);
			return $dirName;
		}
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

	/**
	 * Upload Dir Rules message to display in the CMS
	 */
	function cmsFieldsMessage($dirExists=false){
		$field = null;
		if ($dirExists) {

			//default message
			$defaultMsg = '<em>Files uploaded via the content area will be uploaded to</em><br /> <strong>' .Upload::config()->uploads_folder . '</strong>';

			if($this->owner instanceof UploadDirRulesInterface) {
				$msg = $this->owner->getMessageUploadDirectory();
			}
			if (!$msg) {
				$msg = $defaultMsg;
			}
			$field = new LiteralField('UploadDirectorNote', '
				<div class="field text" id="UploadDirectorNote">
					<label class="left">Upload Directory</label>
					<div class="middleColumn">
						<p style="margin-bottom: 0; padding-top: 0px;">
							' . $msg . '
						</p>
					</div>
				</div>				
				');

		} else {
			//default message
			$defaultMsg = 'Please <strong>choose a name and save</strong> for adding content.';
			
			if($this->owner instanceof UploadDirRulesInterface) {
				$msg = $this->owner->getMessageSaveFirst();
			}
			if (!$msg) {
				$msg = $defaultMsg;
			}
			$field = new LiteralField('ContentPlaceholder', '
				<p class="message notice" >' . $msg . '</p>
			');

		}
		return $field;

	}



	
}