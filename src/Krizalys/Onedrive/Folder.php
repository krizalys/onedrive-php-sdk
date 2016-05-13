<?php

namespace Krizalys\Onedrive;

/**
 * @class Folder
 *
 * A Folder instance is an Object instance referencing to a OneDrive folder. It
 * may contain other OneDrive objects but may not have content.
 */
class Folder extends Object
{
    /**
     * Determines whether the OneDrive object referenced by this Object instance
     * is a folder.
     *
     * @return bool true if the OneDrive object referenced by this Object
     *              instance is a folder, false otherwise.
     */
    public function isFolder()
    {
        return true;
    }

    /**
     * Constructor.
     *
     * @param Client       $client  The Client instance owning this Object
     *                              instance.
     * @param null|string  $id      The unique ID of the OneDrive object
     *                              referenced by this Object instance, or null
     *                              to reference the OneDrive root folder.
     *                              Default: null.
     * @param array|object $options Options to pass to the Object constructor.
     */
    public function __construct(Client $client, $id = null, $options = array())
    {
        parent::__construct($client, $id, $options);
    }

    /**
     * Gets the objects in the OneDrive folder referenced by this Folder instance.
     *
     * @return array The objects in the OneDrive folder referenced by this
     *               Folder instance, as Object instances.
     *
     * @deprecated Use Folder::fetchChildObjects() instead.
     */
    public function fetchObjects()
    {
        // TODO: Log deprecation notice.
        return $this->fetchChildObjects();
    }

    /**
     * Gets the child objects in the OneDrive folder referenced by this Folder
     * instance.
     *
     * @return array The objects in the OneDrive folder referenced by this
     *               Folder instance, as Object instances.
     */
    public function fetchChildObjects()
    {
        return $this->_client->fetchObjects($this->_id);
    }

    /**
     * Gets the descendant objects under the OneDrive folder referenced by this
     * Folder instance.
     *
     * @return array The files in the OneDrive folder referenced by this Folder
     *               instance, as Object instances.
     */
    public function fetchDescendantObjects()
    {
        $files = [];

        foreach ($this->_client->fetchObjects($this->_id) as $file) {
            if ($file->isFolder()) {
                $files = array_merge($file->fetchAllFiles(), $files);
            } else {
                array_push($files, $file);
            }
        }

        return $files;
    }

    /**
     * Creates a folder in the OneDrive folder referenced by this Folder
     * instance.
     *
     * @param string      $name        The name of the OneDrive folder to be
     *                                 created.
     * @param null|string $description The description of the OneDrive folder
     *                                 to be created, or null to create it
     *                                 without a description. Default: null.
     *
     * @return Folder The folder created, as a Folder instance.
     */
    public function createFolder($name, $description = null)
    {
        return $this->_client->createFolder($name, $this->_id, $description);
    }

    /**
     * Creates a file in the OneDrive folder referenced by this Folder instance.
     *
     * @param string          $name    The name of the OneDrive file to be
     *                                 created.
     * @param string|resource $content The content of the OneDrive file to be
     *                                 created, as a string or handle to an
     *                                 already opened file.  In the latter
     *                                 case, the responsibility to close the
     *                                 handle is left to the calling function.
     *                                 Default: ''.
     * @param boolean      $overwrite  Indicate whether you want to overwrite files with the same name.
     *
     * @return File The file created, as a File instance.
     *
     * @throws \Exception Thrown on I/O errors.
     */
    public function createFile($name, $content = '', $overwrite = true)
    {
        return $this->_client->createFile($name, $this->_id, $content, $overwrite);
    }
}
