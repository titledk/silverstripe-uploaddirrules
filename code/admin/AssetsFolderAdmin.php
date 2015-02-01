<?php
/**
 * Extension for {@see LeftAndMainExtension} for ensuring consistent file uploading
 * when working with subsites
 * 
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2015, Title Web Solutions
 */
class AssetsFolderAdmin extends LeftAndMainExtension {

//	public function init() {
//		//setting the uploads directory to make sure all images are saved
//		//according to the rules set in {@see UploadDirRules}
//
//		$owner = $this->owner;
//
//		$link = $owner->Link();
//		//echo $link;
//
//		if (
//			$link == 'admin/pages/' ||
//			$link == 'admin/pages/edit/'
//		) {
//
//			$dirName = UploadDirRules::current_page_directory();
//			if ($dirName) {
//				Upload::config()->uploads_folder = $dirName;
//			}
//
//		}

//	}
}