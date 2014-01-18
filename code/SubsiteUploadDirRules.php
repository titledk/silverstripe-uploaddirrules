<?php
/**
 * Helper for consistent upload directories for sites using the subsites module
 *
 * These rules assure that files are being uploaded to a subdirectory of the "assets" directory.
 * This subdirectory has the name of the current site.
 * Additionally, files are saved in directories, corresponding to the page that is currently being edited.
 * 
 * In order for this to work out-of the box, the following extensions need 
 * to be configured (additionally to the requirements in {@see UploadDirRules}:
 *
 * SiteTree:
 *   extensions:
 *		 - SubsiteUploadDirRules_SiteTreeExtension
 * SiteConfig:
 *   extensions:
 *     - AssetsFolderExtension
 * Subsite:
 *   extensions:
 *     - AssetsFolderExtension
 * 
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2014, Title Web Solutions
 */

class SubsiteUploadDirRules extends UploadDirRules {

	/**
	 * Base upload directory for all sites - whether they are sub sites or normal
	 * If directory doesn't exist, it will be created
	 * 
	 * Returns the base path that the file should be saved in.
	 * E.g. 'title-web-solutions'
	 * 
	 */
	public static function site_upload_directory() {
		$dirName = null;
		$subsite = Subsite::currentSubsite();
		$title = null;
		
		$dirObj = null;
		
		if ($subsite) {
			if ((int) $subsite->AssetsFolderID == 0 || self::$dryrun) {
				$title = $subsite->Title;
				$url = singleton('SiteTree')->generateURLSegment($title);
				//$dirObj = self::find_or_make_folder_site_upload_directory($url, $subsite);
				$dirObj = $subsite->FindOrMakeAssetsFolder($url);
			} else {
				$dirObj = $subsite->AssetsFolder();
			}
		} else {
			$sc = SiteConfig::current_site_config();
			if ((int) $sc->AssetsFolderID == 0 || self::$dryrun) {
				$title = $sc->Title;
				$url = singleton('SiteTree')->generateURLSegment($title);
				//$dirObj = self::find_or_make_folder_site_upload_directory($url, $sc);
				$dirObj = $sc->FindOrMakeAssetsFolder($url);
			} else {
				//$dirObj = $sc->GetAndUpdateAssetsFolder($url);
				$dirObj = $sc->AssetsFolder();
				
			}			
		}
		//$dirName = singleton('SiteTree')->generateURLSegment($title);
		$dirName = str_replace('assets/', '', $dirObj->Filename);
		return $dirName;
	}


}

/**
 * This extension is added to {@see SiteTree} to make pages aware of the 
 * Upload dir rules, force the user to choose a name before adding content,
 * and using that name to create an assets directory
 */
class SubsiteUploadDirRules_SiteTreeExtension extends UploadDirRules_SiteTreeExtension {

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
					$base = UploadDirRules::site_upload_directory();
					$pageUrlPart = UploadDirRules::page_directory_part($this->owner);
					$url = "$base/pages/$pageUrlPart";
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


}