<?php
/**
 * Upload Dir rules follow a pattern, and objects that use those rule have to follow
 * this pattern.
 * Objects that have differnt rules need to implement this interface
 * 
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2013, Title Web Solutions
 */
interface UploadDirRulesInterface {
	
	/**
	 * Calculation of the assets folder directory
	 */
	function getCalcAssetsFolderDirectory();
	
	/**
	 * Message in the "save first" dialog
	 */
	function getMessageSaveFirst();
	
	/**
	 * Message in the "upload directory" label
	 */
	function getMessageUploadDirectory();
	
	
}