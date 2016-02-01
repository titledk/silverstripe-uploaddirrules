<?php
/**
 * Extension for {@see LeftAndMainExtension}.
 * 
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2015, Title Web Solutions
 */
class AssetsFolderAdmin extends LeftAndMainExtension
{
    /**
     * These classes are checked for.
     *
     * @var array
     */
    public static $supported_classes = array(
        'ModelAdmin',
        'CMSPagesController',
        'CMSPageEditController',
        'CMSSettingsController',
    );

    public function init()
    {
        //setting the uploads directory to make sure images uploaded through the content
        //editor are saved the right place

        $curr = LeftAndMain::curr();

        if ($curr) {

            //Debug::dump(get_class($curr));
            //Debug::dump(ClassInfo::ancestry($curr));

            $currClass = null;
            foreach (ClassInfo::ancestry($curr) as $class) {
                foreach (self::$supported_classes as $supported_class) {
                    if ($class == $supported_class) {
                        $currClass = $class;
                    }
                }
            }

            //Debug::dump($currClass);

            //switch (get_class($curr)) {
            switch ($currClass) {

                //Page administration
                case 'CMSPagesController':
                case 'CMSPageEditController':
                    $page = $curr->currentPage();
                    if ($page && $page->hasExtension('AssetsFolderExtension')) {
                        Upload::config()->uploads_folder = $page->getAssetsFolderDirName();
                    }
                    //Debug::dump($page->Title);
                    break;
                case 'ModelAdmin':

                    //For ModelAdmin we're falling back to cookies that we believe to have
                    //been set when setting the cms fields, see AssetFolderExtension::updateCMSFields()
                    //...as it seems to be almost impossible to figure out the current object elsewise
                    //see below for tries
                    //pull requests to fix this welcome!!!

                    //Debug::dump($this->owner->getURLParams());
                    //Debug::dump($this->owner->request->param('ModelClass'));
                    //Debug::dump($this->owner->request->remaining());
                    //Debug::dump($this->owner->request->getVars());
                    //Debug::dump($this->owner->request->params());
                    //Debug::dump($curr->currentPageID());

                    Upload::config()->uploads_folder = Cookie::get('cms-uploaddirrules-uploads-folder');

                    break;

                //Settings
                case 'CMSSettingsController':
                    if (Object::has_extension('SiteConfig', 'AssetsFolderExtension')) {
                        $sc = SiteConfig::current_site_config();
                        Upload::config()->uploads_folder = $sc->getAssetsFolderDirName();
                    }

                default:
            }
        }
    }
}
