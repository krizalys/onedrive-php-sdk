<?php
namespace Onedrive;

/*
 * A File instance is an Object instance referencing a OneDrive file. It may
 * have content but may not contain other OneDrive objects.
 */
class File extends Object {
	/**
	 * Constructor.
	 *
	 * @param  (Client) $client - The Client instance owning this Object instance.
	 * @param  (null|string) $id - The unique ID of the OneDrive object referenced
	 *         by this Object instance.
	 * @param  (array|object) $options - An array/object with one or more of the
	 *         following keys/properties:
	 *           (string) parent_id - The unique ID of the parent OneDrive folder
	 *           of this object.
	 *           (string) name - The name of this object.
	 *           (string) description - The description of this object. May be
	 *           empty.
	 *           (int) size - The size of this object, in bytes.
	 *           (string) created_time - The creation time, as a RFC date/time.
	 *           (string) updated_time - The last modification time, as a RFC
	 *           date/time.
	 *         Default: array().
	 */
	public function __construct(Client $client, $id, $options = array()) {
		parent::__construct($client, $id, $options);
	}

	/**
	 * Fetches the content of the OneDrive file referenced by this File instance.
	 *
	 * @return (string) The content of the OneDrive file referenced by this File
	 * instance.
	 */
	// TODO: should somewhat return the content-type as well; this information is
	// not disclosed by OneDrive
	public function fetchContent() {
		return $this->_client->apiGet($this->_id . '/content');
	}

	/**
	 * Copies the OneDrive file referenced by this File instance into another
	 * OneDrive folder.
	 *
	 * @param  (null|string) The unique ID of the OneDrive folder into which to
	 *         copy the OneDrive file referenced by this File instance, or null to
	 *         copy it in the OneDrive root folder. Default: null.
	 */
	public function copy($destinationId = null) {
		$this->_client->copyFile($this->_id, $destinationId);
	}
}
