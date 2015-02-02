<?php
//only run this test if BaseSubsiteTest exists
if (class_exists('BaseSubsiteTest')) {

/**
 * Class SubsiteUploadDirRulesTest
 *
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2015, Title Web Solutions
 */
class SubsiteUploadDirRulesTest extends BaseSubsiteTest {

	static $fixture_file = 'subsites/tests/SubsiteTest.yml';
	
	function setUp() {
		parent::setUp();

		$this->origStrictSubdomainMatching = Subsite::$strict_subdomain_matching;
		Subsite::$strict_subdomain_matching = false;
	}

	public function tearDown() {
		parent::tearDown();

		Subsite::$strict_subdomain_matching = $this->origStrictSubdomainMatching;
	}

	public function test_calc_directory_for_subsite() {

		//Template:
		$subsite = $this->objFromFixture('Subsite','main');

		//check calculation
		$this->assertEquals(
			'template',
			SubsiteUploadDirRules::calc_directory_for_subsite($subsite)
		);
		//check the generated folder
		Subsite::changeSubsite($subsite->ID);
		$this->assertEquals(
			'template',
			SubsiteUploadDirRules::get_directory_for_current_subsite()
		);
		
		

		//Subsite1 Template:
		$subsite = $this->objFromFixture('Subsite','subsite1');
		
		//check calculation
		$this->assertEquals(
			'subsite1-template',
			SubsiteUploadDirRules::calc_directory_for_subsite($subsite)
		);
		//check the generated folder
		Subsite::changeSubsite($subsite->ID);
		$this->assertEquals(
			'subsite1-template',
			SubsiteUploadDirRules::get_directory_for_current_subsite()
		);

		
		//Test 3:
		$subsite = $this->objFromFixture('Subsite','domaintest3');

		//check calculation
		$this->assertEquals(
			'test-3',
			SubsiteUploadDirRules::calc_directory_for_subsite($subsite)
		);
		//check the generated folder
		Subsite::changeSubsite($subsite->ID);
		$this->assertEquals(
			'test-3',
			SubsiteUploadDirRules::get_directory_for_current_subsite()
		);


		Subsite::changeSubsite(0);


		
		
//		if (class_exists('Subsite') && Object::has_extension('Subsite', 'AssetsFolderExtension')) {
//
//			$subsite = Subsite::get()->first();
//
//
//			//Setting subsite via $_GET
//			//this is not bes practice, but this seems to be the only way that works
//			//when running this over the command line
//
//			//Subsite::changeSubsite($subsite->ID);
//			$_GET['SubsiteID'] = $subsite->ID;
//
//			$this->assertEquals('my-test-subsite', SubsiteUploadDirRules::calc_directory_for_subsite());
//		}

	}
}

}