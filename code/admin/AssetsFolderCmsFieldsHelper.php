<?php
/**
 * Helper for handling assets folder related CMS fields
 * TODO this is a first step but could take further cleanup
 *
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2016, Title Web Solutions
 */
class AssetsFolderCmsFieldsHelper {

    public static function assetsFolderField($obj, $dirExists) {
        $field = null;
        $msg = null;

        if ($dirExists) {

            //Message
            $defaultMsg = '<em>Files uploaded via the content area will be uploaded to</em>'.
                '<br /> <strong>'.Upload::config()->uploads_folder.'</strong>';
            if ($obj instanceof UploadDirRulesInterface) {
                $msg = $obj->getMessageUploadDirectory();
            }
            if (!$msg) {
                $msg = $defaultMsg;
            }

            //TODO these could also be global settings
            $manageAble = true;
            $editable = true;

            //As this is happening from the subsites administration, when editing a subsite
            //you'd probably be on another site, and hence can't access the site's files anyway
            if ($obj->ClassName == 'Subsite') {
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

                $dir = $obj->AssetsFolder();
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
            if ($obj instanceof UploadDirRulesInterface) {
                $msg = $obj->getMessageSaveFirst();
            }
            if (!$msg) {
                $msg = $defaultMsg;
            }
            //preview calculated assets folder
            //$msg = $msg . ' (' . $this->owner->getCalcAssetsFolderDirectory() . ')';
            $field = new LiteralField('UploadDirRulesNote', '
                <p class="message notice" >'.$msg.'</p>
            ');
        }

        return $field;
    }


    /**
     * @param FieldList $fields
     * @return FieldList
     */
    public static function updateAssetsFolderCMSField($obj, FieldList $fields)
    {

        //prepopulating object with asset folder - if allowed
        if ($obj->AssetsFolderID == 0) {
            $url = $obj->assetsFolderUrlToBeWritten();

            if ($url) {
                //this creates the directory, and attaches it to the page,
                //as well as saving the object one more time - with the attached folder
                $obj->findOrMakeAssetsFolder($url, false);
            }
        }


        $dirName = $obj->getAssetsFolderDirName();
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
        $field = $obj->getAssetsFolderField($dirExists);
        $fields->removeByName('AssetsFolder');

        //Adding fields - to tab or just pushing
        $isPage = false;
        $ancestry = $obj->getClassAncestry();
        foreach ($ancestry as $c) {
            if ($c == 'SiteTree') {
                $isPage = true;
            }
        }

        if ($dirExists) {

            $field = ToggleCompositeField::create(
                'UploadDirRulesNotes',
                'Upload Rules (' . $dirName . ')',
                [
                    $field
                ]
            );


            //configurable tab
            $tab = $obj->config()->uploaddirrules_fieldtab;
            if (isset($tab)) {
                $fields->addFieldToTab($tab, $field);
            } else {
                if ($isPage) {
                    //$fields->addFieldToTab('Root.Main', $htmlField, 'Content');
                    $fields->addFieldToTab('Root.Main', $field);
                } else {
                    //TODO this should be configurable
                    switch ($obj->ClassName) {
                        case 'Subsite':
                            $fields->addFieldToTab('Root.Configuration', $field);
                            break;

                        case 'SiteConfig':
                        case 'GenericContentBlock':
                            $fields->addFieldToTab('Root.Main', $field);
                            break;

                        default:
                            $fields->push($field);
                    }
                }
            }
        } else {
            $noteTab = new Tab('Note', 'Note', $field);
            $fields->insertBefore($noteTab, 'Main');
        }

        return $fields;
    }
}
