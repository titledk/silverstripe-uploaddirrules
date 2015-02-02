# Upload Dir Rules

_by title.dk/Anselm Christophersen_


Rules to automatically keep the SilverStripe assets directory tidy.


This module is currently being remodelled - use the `legacy` branch for now.


## Planned enhancements

* asset folder editable from CMS - a little like `urlsegment`
* **[DONE]** create asset folder on first page (draft) create
* **[UNDER DEVELOPMENT]** allowing to rename subsite assets folder
* method that lists all rules for printing or display, e.g.
	* pages: `/my-subsite/pages/`
	* siteconfig: `/my-subsite/site`
	* etc.

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



## Unit tests

This module will contain unit tests. Run like this:

	vendor/bin/phpunit uploaddirrules/tests

Make sure that you've got `phpunit` set up:

	composer require --dev "phpunit/phpunit:*@stable"

## Test task


	php ./public/framework/cli-script.php /UploadDirRulesTestTask

add ` flush=1` for first run...