<?php
/**
 * Test task for helping upload dir rules development and debugging.
 *
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2015, Title Web Solutions
 */
class UploadDirRulesTestTask extends CliController
{
    public function process()
    {
        $this->doEcho();
        $this->subsitesTests();

        $this->doEcho();
    }

    public function subsitesTests()
    {
        $this->doEcho('Subsites', true);

        $subsites = Subsite::get()
            ->sort('ID ASC');

        foreach ($subsites as $s) {
            $this->doEcho("{$s->Title} (#{$s->ID}):");
            //Setting subsite via $_GET
            //this is not bes practice, but this seems to be the only way that works
            //when running this over the command line
            $_GET['SubsiteID'] = $s->ID;
            $this->doEcho(SubsiteUploadDirRules::get_directory_for_subsite());

            $this->doEcho();
        }
    }

    private function doEcho($str = null, $heading = false)
    {
        if ($heading) {
            echo "###################################\n";
        }
        echo "$str\n";
        if ($heading) {
            echo "###################################\n\n";
        }
    }
}
