<?php
/**
 * This extension can be added to any object that should have a relationship
 * to a folder inside /assets.
 *
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2015, Title Web Solutions
 */
class AssetsFolderExtension extends DataExtension
{
    public static $has_one = array(
        'AssetsFolder' => 'Folder',
    );

    /**
     * Displaying assets folder relation in CMS fields
     * as well as setting the global upload config.
     *
     * @param FieldList $fields
     *
     * @return FieldList|void
     */
    public function updateCMSFields(FieldList $fields)
    {
        return $this->owner->updateAssetsFolderCMSField($fields);
    }

    public function updateAssetsFolderCMSField(FieldList $fields) {
        return AssetsFolderCmsFieldsHelper::updateAssetsFolderCMSField($this->owner, $fields);
    }


    /**
     * Creation and association of assets folder,
     * once a data object has been created (and is ready for it).
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();

        //creation will only be considered if the object has no folder relation
        if ($this->owner->AssetsFolderID == 0) {
            $url = $this->owner->assetsFolderUrlToBeWritten();

            if ($url) {
                //this creates the directory, and attaches it to the page,
                //as well as saving the object one more time - with the attached folder
                $this->findOrMakeAssetsFolder($url, true);
            }
        }
    }

    //TODO this should NOT be public, but I'm not sure whether there's a way around it
    public function assetsFolderUrlToBeWritten() {
        $url = null;

        //the default rules only require the object to have an ID
        //but more sophisticated rules might require more - e.g. a title to be set
        //thus we check if the object is ready for folder creation - if custom rules
        //(UploadDirRulesInterface) have been set
        if (method_exists($this->owner, 'getReadyForFolderCreation')) {
            if (!$this->owner->getReadyForFolderCreation()) {
                return false;
            }
        }

        //check if the page we're having is implementing the UploadDirRulesInterface
        //for rule customization
        if ($this->owner instanceof UploadDirRulesInterface) {
            $url = $this->owner->getCalcAssetsFolderDirectory();
        } else {
            //else use the default settings

            $class = UploadDirRules::get_rules_class();
            $url = $class::calc_full_directory_for_object($this->owner);
        }
        return $url;
    }

    /**
     * Find or make assets folder
     * called from onBeforeWrite.
     *
     * @param string $url
     * @param bool   $doWrite
     *
     * @return Folder|null
     */
    public function findOrMakeAssetsFolder($url, $doWrite = true)
    {
        $owner = $this->owner;
        $dir = Folder::find_or_make($url);
        $owner->AssetsFolderID = $dir->ID;
        if ($doWrite) {
            $owner->write();
        }

        //Special case for when creating a new subsite
        //the directory will need to be associated with the subsite
        if ($owner->ClassName == 'Subsite') {
            $dir->SubsiteID = $owner->ID;
            $dir->write();
        }

        return $dir;
    }

    /**
     * Name of the associated assets folder.
     *
     * @return string|null
     */
    public function getAssetsFolderDirName()
    {
        if ($this->owner->getField('AssetsFolderID') != 0) {
            $dirObj = $this->owner->AssetsFolder();
            $dirName = str_replace('assets/', '', $dirObj->Filename);

            return $dirName;
        }
    }

    /**
     * Upload Dir Rules message and fields to display in the CMS.
     *
     * @param bool $dirExists
     *
     * @return LiteralField|null
     */
    public function getAssetsFolderField($dirExists)
    {
        return AssetsFolderCmsFieldsHelper::assetsFolderField($this->owner, $dirExists);
    }
}
