<?php
/**
 * Helper for consistent upload directories.
 * 
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2015, Title Web Solutions
 */
class UploadDirRules
{
    /**
     * Global rules for asset directories
     * These can be overridden on each class.
     * 
     * @param DataObject $do
     *
     * @return string
     */
    public static function calc_full_directory_for_object(DataObject $do)
    {
        $str = self::calc_base_directory_for_object($do);

        return $str;
    }

    /**
     * Base rules.
     * 
     * @param DataObject $do
     *
     * @return string
     */
    public static function calc_base_directory_for_object(DataObject $do)
    {

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
     * TODO this could easily be amended to be configurable, so that custom rules could be used.
     *
     * @return string
     */
    public static function get_rules_class()
    {
        $class = 'UploadDirRules';
        if (class_exists('Subsite') && Object::has_extension('Subsite', 'AssetsFolderExtension')) {
            $class = 'SubsiteUploadDirRules';
        }

        return $class;
    }
}
