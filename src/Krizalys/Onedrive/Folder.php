<?php

namespace Krizalys\Onedrive;

use Psr\Log\LogLevel;

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
        $message = sprintf(
            '%s() is deprecated and will be removed in a future version; use %s::fetchChildObject() instead',
            __METHOD__,
            __CLASS__
        );

        $this->_client->log(LogLevel::WARNING, $message);
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
        $objects = array();

        foreach ($this->fetchChildObjects() as $object) {
            if ($object->isFolder()) {
                $objects = array_merge($object->fetchDescendantObjects(), $objects);
            } else {
                array_push($objects, $object);
            }
        }

        return $objects;
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
     *                                 already opened file.  In the latter case,
     *                                 the responsibility to close the handle is
     *                                 is left to the calling function. Default:
     *                                 ''.
     * @param array           $options The options.
     *
     * @return File The file created, as a File instance.
     *
     * @throws \Exception Thrown on I/O errors.
     */
    public function createFile($name, $content = '', array $options = array())
    {
        $client = $this->_client;

        $options = array_merge(array(
            'name_conflict_behavior' => $client->getNameConflictBehavior(),
            'stream_back_end'        => $client->getStreamBackEnd(),
        ), $options);

        return $this->_client->createFile($name, $this->_id, $content, $options);
    }
}
