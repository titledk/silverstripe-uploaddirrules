<?php
/**
 * Upload Dir rules follow a pattern, and objects that use those rule have to follow
 * this pattern.
 * Objects that have different rules need to implement this interface
 * 
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2015, Title Web Solutions
 */
interface UploadDirRulesInterface {

	/**
	 * Calculation of the assets folder directory
	 *
	 * @return string
	 */
	function getCalcAssetsFolderDirectory();
	
	/**
	 * Message in the "save first" dialog
	 * Return "false" for default message
	 * 
	 * @return string|false
	 */
	function getMessageSaveFirst();
	
	/**
	 * Message in the "upload directory" label
	 * Return "false" for default message
	 * 
	 * @return string|false
	 */
	function getMessageUploadDirectory();

	/**
	 * Folder will only be created when the object is ready for it
	 *
	 * @return boolean
	 */
	function getReadyForFolderCreation();
	
	
}