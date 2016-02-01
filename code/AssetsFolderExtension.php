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

        //The Upload Directory field
        //TODO make it configurable if field should be shown
        //TODO make field placement configurable
        $field = $this->getAssetsFolderField($dirExists);

        //Adding fields - to tab or just pushing
        $isPage = false;
        $ancestry = $this->owner->getClassAncestry();
        foreach ($ancestry as $c) {
            if ($c == 'SiteTree') {
                $isPage = true;
            }
        }

        //configurable tab
        $tab = $this->owner->config()->uploaddirrules_fieldtab;
        if (isset($tab)) {
            $fields->addFieldToTab($tab, $field);
        } else {
            if ($isPage) {
                //$fields->addFieldToTab('Root.Main', $htmlField, 'Content');
                $fields->addFieldToTab('Root.Main', $field);
            } else {
                switch ($this->owner->ClassName) {

                    case 'Subsite':
                        $fields->addFieldToTab('Root.Configuration', $field);
                        break;

                    case 'SiteConfig':
                        $fields->addFieldToTab('Root.Main', $field);
                        break;

                    default:
                        $fields->push($field);
                }
            }
        }

        return $fields;
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

            //the default rules only require the object to have an ID
            //but more sophisticated rules might require more - e.g. a title to be set
            //thus we check if the object is ready for folder creation - if custom rules
            //(UploadDirRulesInterface) have been set
            if ($this->owner instanceof UploadDirRulesInterface) {
                if (!$this->owner->getReadyForFolderCreation()) {
                    return false;
                }
            }

            $url = null;
            //check if the page we're having is implementing the UploadDirRulesInterface
            //for rule customization
            if ($this->owner instanceof UploadDirRulesInterface) {
                $url = $this->owner->getCalcAssetsFolderDirectory();
            } else {
                //else use the default settings

                $class = UploadDirRules::get_rules_class();
                $url = $class::calc_full_directory_for_object($this->owner);
            }

            if ($url) {
                //this creates the directory, and attaches it to the page,
                //as well as saving the object one more time - with the attached folder
                $this->findOrMakeAssetsFolder($url, true);
            }
        }
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
    protected function findOrMakeAssetsFolder($url, $doWrite = true)
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
    protected function getAssetsFolderField()
    {
        $field = null;
        $msg = null;

        $dirName = $this->owner->getAssetsFolderDirName();
        $dirExists = false;
        if ($dirName) {
            $dirExists = true;
        }

        if ($dirExists) {

            //Message
            $defaultMsg = '<em>Files uploaded via the content area will be uploaded to</em>'.
                '<br /> <strong>'.Upload::config()->uploads_folder.'</strong>';
            if ($this->owner instanceof UploadDirRulesInterface) {
                $msg = $this->owner->getMessageUploadDirectory();
            }
            if (!$msg) {
                $msg = $defaultMsg;
            }

            //TODO these could also be global settings
            $manageAble = true;
            $editable = true;

            //As this is happening from the subsites administration, when editing a subsite
            //you'd probably be on another site, and hence can't access the site's files anyway
            if ($this->owner->ClassName == 'Subsite') {
                $manageAble = false;
                $editable = false;
            }

            if ($editable) {

                //Asset folder is editable

                $field1 = new TreeDropdownField('AssetsFolderID', 'Change Directory:', 'Folder');
                $field1->setRightTitle('Directory changes take place after saving.');

                //Dropdown field style adjustments
                //TODO move this to an external stylesheet as these styles don't kick in on AJAX loads
                Requirements::customCSS('
                    #TreeDropdownField_Form_EditForm_AssetsFolderID {
                        min-width: 260px;
                    }
                    .UploadDirectoryFields .fieldgroup label {
                        padding: 0 0 4px;
                    }
                ');

                $dir = $this->owner->AssetsFolder();
                $filescount = File::get()->filter(array('ParentID' => $dir->ID))->count();

                $manageButton = null;
                if ($manageAble) {
                    $manageButton =
                    "<a href='/admin/assets/show/".$dir->ID."' class='ss-ui-button ss-ui-button-small ui-button'>
                        Manage Files (".$filescount.')</a>';
                }

                $field2 = new LiteralField('UploadDirRulesNote',
                        "<div style='margin-bottom:10px;margin-right:16px;'>$msg</div>".$manageButton);

                $field = new FieldGroup(array(
                    $field2,
                    $field1,
                ));

                $field->setTitle('Upload Directory');
                $field->addExtraClass('UploadDirectoryFields');
                $field->setName('UploadDirectoryFields');
            } else {

                //Asset folder is not editable

                $field = new LiteralField('UploadDirRulesNote', '
                    <div class="field text" id="UploadDirRulesNote">
                        <label class="left">Upload Directory</label>
                        <div class="middleColumn">
                            <p style="margin-bottom: 0; padding-top: 0px;">
                                '.$msg.'
                                <br />
                                <em>If you need to edit or change this folder, please contact your administrator.</em>
                            </p>
                        </div>
                    </div>
                    ');
            }
        } else {

            //Message
            $defaultMsg = 'Please <strong>choose a name and save</strong> for adding content.';
            if ($this->owner instanceof UploadDirRulesInterface) {
                $msg = $this->owner->getMessageSaveFirst();
            }
            if (!$msg) {
                $msg = $defaultMsg;
            }
            $field = new LiteralField('UploadDirRulesNote', '
                <p class="message notice" >'.$msg.'</p>
            ');
        }

        return $field;
    }
}
