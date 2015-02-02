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
			//Setting and showing the uploads folder
			//This doesn't work for iframe uploads, there we need 
			//the AssetsFolderAdmin extension to LeftAndMain
			Upload::config()->uploads_folder = $dirName;
			
			//Cookie fallback for moments where it's impossible to figure
			//out the uploads folder through the leftandmain controller.
			//e.g. ModelAdmin - {@see AssetsFolderAdmin}
			Cookie::set('cms-uploaddirrules-uploads-folder', $dirName);
		}

		//Fields
		//TODO make it configurable if they should be shown
		//TODO make field placement configurable
		$htmlField = $this->cmsFieldsMessage($dirExists);
		
		
		//Adding fields - to tab or just pushing
		$isPage = false;
		$ancestry = $this->owner->getClassAncestry();
		foreach ($ancestry as $c) {
			if ($c == 'SiteTree') {
				$isPage = true;
			}
		}
		if ($isPage) {
			//$fields->addFieldToTab('Root.Main', $htmlField, 'Content');
			$fields->addFieldToTab('Root.Main', $htmlField);
		} else {

			//TODO make this configurable
			switch ($this->owner->ClassName) {

				case 'Subsite':
					$fields->addFieldToTab('Root.Configuration', $htmlField);
					break;

				case 'SiteConfig':
					$fields->addFieldToTab('Root.Main', $htmlField);
					break;

				default:
					$fields->push($htmlField);
			}
			
		}

		return $fields;
	}
	

	/**
	 * Creation and association of assets folder,
	 * once a data object has been created (and is ready for it)
	 */
	function onAfterWrite() {
		parent::onAfterWrite();

		//creation will only be considered if the object has no folder relation
		if ($this->owner->AssetsFolderID == 0) {
			
			//the default rules only require the object to have an ID
			//but more sophisticated rules might require more - e.g. a title to be set
			//thus we check if the object is ready for folder creation - if custom rules
			//(UploadDirRulesInterface) have been set
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
				//this creates the directory, and attaches it to the page,
				//as well as saving the object one more time - with the attached folder
				$dirObj = $this->findOrMakeAssetsFolder($url, true);
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
			//$field = new LiteralField('UploadDirRulesNote', '
			//	<div class="field text" id="UploadDirRulesNote">
			//		<label class="left">Upload Directory</label>
			//		<div class="middleColumn">
			//			<p style="margin-bottom: 0; padding-top: 0px;">
			//				' . $msg . '
			//			</p>
			//		</div>
			//	</div>
			//	');
			
			//$field = new AssetsFolderURLSegmentField('UploadDir', 'Upload Directory');
			//$baseLink = Controller::join_links (
			//	Director::absoluteBaseURL(),
			//	'assets/'
			//	//TODO the subsite part should go here as well
			//);
			//$field->setURLPrefix($baseLink);
			//$field->setValue(Upload::config()->uploads_folder);
			//$field->setAssetsFolderID($this->owner->AssetsFolderID);

			//$field->setHelpText('Note that if you change this directory, you might need to update links to any uploaded images in the content area.');


			$field1 = new TreeDropdownField("AssetsFolderID", "Upload Directory", "Folder");
			$field1->setRightTitle('Files on this object will be uploaded to this directory');

			$dir = $this->owner->AssetsFolder();
			$filescount = File::get()->filter( array("ParentID"=>$dir->ID) )->count();

			$field2 = new LiteralField("addnew",
					"<p><a href='/admin/assets/show/".$dir->ID."' class='ss-ui-button ss-ui-action-constructive ui-button' data-icon=add>
					Manage Upload Directory (".$filescount.")</span></a></p>");
			
			
			$field = new CompositeField(array(
				$field1,
				$field2
			));
			
			
			
			
			
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
