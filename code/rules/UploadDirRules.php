<?php
/**
 * Helper for consistent upload directories
 * 
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2015, Title Web Solutions
 */
class UploadDirRules {

	/**
	 * Global rules for asset directories
	 * These can be overridden on each class
	 * 
	 * @param DataObject $do
	 * @return string
	 */
	public static function calc_full_directory_for_object(DataObject $do){
		$str = self::calc_base_directory_for_object($do);
		return $str;
	}

	/**
	 * Base rules
	 * 
	 * @param DataObject $do
	 * @return string
	 */
	public static function calc_base_directory_for_object(DataObject $do){
		
		//the 2 base options - dataobjects / pages
		$str = 'dataobjects';
		$ancestry = $do->getClassAncestry();
		foreach ($ancestry as $c) {
			if ($c == 'SiteTree') {
				$str = 'pages';
			}
		}
		
		//other options
		switch ($do->ClassName) {
			
			//case 'Subsite':
			//	$str = 'subsites';
			//	break;

			case 'SiteConfig':
				$str = 'site';
				break;
			
			default:
		}

		return $str;
	}



	/**
	 * Getter for the rules class
	 * Based on your needs, different rule classes could be used
	 * The module comes bundled with the base rules, and subsite rules
	 * TODO this could easily be amended to be configurable, so that custom rules could be used
	 *
	 * @return string
	 */
	public static function get_rules_class() {
		$class = 'UploadDirRules';
		if (class_exists('Subsite') && Object::has_extension('Subsite', 'AssetsFolderExtension')) {
			$class = 'SubsiteUploadDirRules';
		}
		return $class;
	}
	

}





///**
// * This is for pages
// * 
// * This extension is added to {@see SiteTree} to make pages aware of the
// * Upload dir rules, force the user to choose a name before adding content,
// * and using that name to create an assets directory
// */
//class UploadDirRules_SiteTreeExtension extends DataExtension {
//
//	function updateCMSFields(FieldList $fields) {
//
//		//Don't allow any content creation as long as we don't have an associated
//		//assets directory
//		if ($this->owner->AssetsFolderID == 0) {
//			$fields->replaceField('Content', new HiddenField('Content'));
//			$fields->removeByName('Metadata');
//
//			$htmlField = $this->owner->cmsFieldsMessage(false);
//			$fields->addFieldToTab('Root.Main', $htmlField, 'Title');
//
//		} else {
//			$dirName = UploadDirRules::current_page_directory($this->owner);
//			Upload::config()->uploads_folder = $dirName;
//
//			$htmlField = $this->owner->cmsFieldsMessage(true);
//
//			$fields->addFieldToTab('Root.Main', $htmlField, 'Content');
//
//		}
//
//		return $fields;
//	}
//
//}
//
