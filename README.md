# Upload Dir Rules

_by title.dk/Anselm Christophersen_

Rules and utilities to keep the SilverStripe assets directory tidy.

By installing this module and adding extensions to the objects you want affected,
these objects will receive an associated folder inside of assets.
Now instead of bluntly uploading everything to `assets/Uploads` your site will
upload files more controlled - defaults are set, which are easy to override.

Each object's folder can subsequently easily be changed through the admin, which
allows for sophisticated files & assets strategies.

![Administration](docs/img/admin.png)


**Upload rules are enforced both when uploading through an upload field, but also
when uploading through the text editor**
_(thanks to the `AssetsFolderAdmin` extension)_

This is especially helpful when working with [Subsites](https://github.com/silverstripe/silverstripe-subsites/)
(we're specifically catering for those with `SubsitesUploadDirRules`), but also helpful
in general, or when needing a basic [gallery](https://github.com/titledk/silverstripe-gallery),
a downloads page, or anything else where you wont' want your files scattered all over the place.


## Configuration example

    SiteTree:
      extensions:
        - AssetsFolderExtension
    SiteConfig:
      extensions:
        - AssetsFolderExtension
    Subsite:
      extensions:
        - AssetsFolderExtension
    LeftAndMain:
      extensions:
        - AssetsFolderAdmin



## Basic rules

The basic rules are defined in `UploadDirRules::calc_base_directory_for_object()`, basically
putting all files related to `DataObject` into a `dataobjects` folder, everything related to
`SiteTree` into a `pages` folder and everything related to `SiteConfig` into a `site` folder.



## Overriding default upload dir rules

Just implement the `UploadDirRulesInterface`.
Customization through SilverStripe's config is planned, send a pull request if you need it!

Rules can be anything, from just being a common folder to containing id and or title.

### Example

```php
// Create directory based on the page name
function getCalcAssetsFolderDirectory() {
    if ($this->ID) {
        $filter = URLSegmentFilter::create();
        return $filter->filter($this->Title);
    }
}
function getMessageSaveFirst(){
    return 'Please pick a name and save to create corresponding directory';
}
function getMessageUploadDirectory() {
    return null;
}
// Make sure that the directory is NOT saved before a page name has been chosen
function getReadyForFolderCreation() {
    if ($this->Title != 'New ' . self::$singular_name) {
        return true;
    }
}
```

## Planned enhancements

* method that lists all rules for printing or display, alseo those set via `UploadDirRulesInterface` e.g.
	* pages: `/my-subsite/pages/`
	* siteconfig: `/my-subsite/site`
	* etc.
* method that lists all objects and their asset folders
	* mainly for review / checking up, will probably be used on `UploadDirRulesTestTask`

## Unit tests

This module will contain unit tests. Run like this:

	vendor/bin/phpunit uploaddirrules/tests

Make sure that you've got `phpunit` set up:

	composer require --dev "phpunit/phpunit:*@stable"

## Test task


	php ./public/framework/cli-script.php /UploadDirRulesTestTask

add ` flush=1` for first run...
