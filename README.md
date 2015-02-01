# Upload Dir Rules

_by title.dk/Anselm Christophersen_


Rules to automatically keep the SilverStripe assets directory tidy.


This module is currently being remodelled - use the `legacy` branch for now.



## Unit tests

This module will contain unit tests. Run like this:

	vendor/bin/phpunit uploaddirrules/tests

Make sure that you've got `phpunit` set up:

	composer require --dev "phpunit/phpunit:*@stable"

## Test task


	php ./public/framework/cli-script.php /UploadDirRulesTestTask

add ` flush=1` for first run...