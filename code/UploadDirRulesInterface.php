<?php
/**
 * Upload Dir rules follow a pattern, and objects that use those rule have to follow
 * this pattern.
 * Objects that have different rules need to implement this interface.
 *
 * @author Anselm Christophersen <ac@title.dk>
 * @copyright Copyright (c) 2015, Title Web Solutions
 */
interface UploadDirRulesInterface
{
    /**
     * Calculation of the assets folder directory.
     * If this returns false/null, no directory is created
     *
     * @return string
     */
    public function getCalcAssetsFolderDirectory();

    //TODO below should be optional
    //They are still used, but not required

//    /**
//     * Message in the "save first" dialog
//     * Return "false" for default message.
//     *
//     * @return string|false
//     */
//    public function getMessageSaveFirst();
//
//    /**
//     * Message in the "upload directory" label
//     * Return "false" for default message.
//     *
//     * @return string|false
//     */
//    public function getMessageUploadDirectory();
//
//    /**
//     * Folder will only be created when the object is ready for it.
//     * TODO this one is practically obsolete if getCalcAssetsFolderDirectory can return null
//     *
//     * @return bool
//     */
//    public function getReadyForFolderCreation();
}
