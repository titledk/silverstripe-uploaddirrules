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

	/**
	 * Name of the associated assets folder
	 */
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
		
		//If upload dir rules have been configured to not do an onbefore write
		//do nothing
		if (UploadDirRules::config()->noOnBeforWrite) {
			return;
		}
		
		
		if (($this->owner->ID) > 0 && ($this->owner->AssetsFolderID) == 0) {
			$className = $this->owner->ClassName;
			$translatedClassName = singleton($className)->i18n_singular_name();

			//This will only work for the languages defined here,
			//at some point the supported languages could go into a configuration file
			$title = $this->owner->Title;
			if (
				$title != "New $translatedClassName" && //English
				$title != "Neue $translatedClassName" //German
			) {

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
		$msg = null;
		if ($dirExists) {
			//default message
			$defaultMsg = '<em>Files uploaded via the content area will be uploaded to</em><br /> <strong>' .Upload::config()->uploads_folder . '</strong>';

			if($this->owner instanceof UploadDirRulesInterface) {
				$msg = $this->owner->getMessageUploadDirectory();
			}
			if (!$msg) {
				$msg = $defaultMsg;
			}
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
			//default message
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