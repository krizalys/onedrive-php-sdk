<?php
namespace Onedrive;

/*
 * A Folder instance is an Object instance referencing to a OneDrive folder. It
 * may contain other OneDrive objects but may not have content.
 */
class Folder extends Object {
	/**
	 * Determines whether the OneDrive object referenced by this Object instance
	 * is a folder.
	 *
	 * @return (bool) true if the OneDrive object referenced by this Object
	 *         instance is a folder, false otherwise.
	 */
	public function isFolder() {
		return true;
	}

	/**
	 * Constructor.
	 *
	 * @param  (Client) $client - The Client instance owning this Object instance.
	 * @param  (null|string) $id - The unique ID of the OneDrive object referenced
	 *         by this Object instance, or null to reference the OneDrive root
	 *         folder. Default: null.
	 * @param  (array|object) $options.
	 */
	public function __construct(Client $client, $id = null, $options = array()) {
		parent::__construct($client, $id, $options);
	}

	/**
	 * Gets the objects in the OneDrive folder referenced by this Folder instance.
	 *
	 * @return (array) The objects in the OneDrive folder referenced by this
	 *         Folder instance, as Object instances.
	 */
	public function fetchObjects() {
		return $this->_client->fetchObjects($this->_id);
	}

	/**
	 * Creates a folder in the OneDrive folder referenced by this Folder instance.
	 *
	 * @param  (string) $name - The name of the OneDrive folder to be created.
	 * @param  (null|string) $description - The description of the OneDrive folder
	 *         to be created, or null to create it without a description. Default:
	 *         null.
	 * @return (Folder) The folder created, as a Folder instance.
	 */
	public function createFolder($name, $description = null) {
		return $this->_client->createFolder($name, $this->_id, $description);
	}

	/**
	 * Creates a file in the OneDrive folder referenced by this Folder instance.
	 *
	 * @param  (string) $name - The name of the OneDrive file to be created.
	 * @param  (string) $content - The content of the OneDrive file to be created.
	 *         Default: ''.
	 * @return (File) The file created, as a File instance.
	 * @throw  (\Exception) Thrown on I/O errors.
	 */
	public function createFile($name, $content = '') {
		return $this->_client->createFile($name, $this->_id, $content);
	}
}
