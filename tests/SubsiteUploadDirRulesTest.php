<?php
/**
 * Class SubsiteUploadDirRulesTest
 *
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2015, Title Web Solutions
 */
class SubsiteUploadDirRulesTest extends SapphireTest {

	function setUp() {
		parent::setUp();

		// create 2 pages
		for($i=0; $i<2; $i++) {
			$page = new Page(array('Title' => "Page $i"));
			$page->write();
			$page->publish('Stage', 'Live');
		}

		if (class_exists('Subsite') && Object::has_extension('Subsite', 'AssetsFolderExtension')) {
			$subsite = new Subsite();
			$subsite->Title = 'My Test Subsite';
			$subsite->write();
		}

		// reset configuration for the test.
		Config::nest();
		Config::inst()->update('Foo', 'bar', 'Hello!');
	}

	public function tearDown() {
		// restores the config variables
		Config::unnest();

		parent::tearDown();
	}

	public function test_calc_directory_for_subsite() {
		if (class_exists('Subsite') && Object::has_extension('Subsite', 'AssetsFolderExtension')) {

			$subsite = Subsite::get()->first();


			//Setting subsite via $_GET
			//this is not bes practice, but this seems to be the only way that works
			//when running this over the command line

			//Subsite::changeSubsite($subsite->ID);
			$_GET['SubsiteID'] = $subsite->ID;

			$this->assertEquals('my-test-subsite', SubsiteUploadDirRules::calc_directory_for_subsite());
		}

	}
}