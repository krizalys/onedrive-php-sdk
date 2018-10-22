<?php

namespace Krizalys\Onedrive;

/**
 * @class File
 *
 * A File instance is a DriveItem instance referencing a OneDrive file. It may
 * have content but may not contain other OneDrive drive items.
 */
class File extends DriveItem
{
    /**
     * Constructor.
     *
     * @param Client $client
     *        The Client instance owning this DriveItem instance.
     * @param null|string $id
     *        The unique ID of the OneDrive drive item referenced by this
     *        DriveItem instance.
     * @param array|object $options
     *        Options to pass to the DriveItem constructor.
     */
    public function __construct(Client $client, $id, $options = [])
    {
        parent::__construct($client, $id, $options);
    }

    /**
     * Fetches the content of the OneDrive file referenced by this File
     * instance.
     *
     * @param array $options
     *        Extra cURL options to apply.
     *
     * @return string
     *         The content of the OneDrive file referenced by this File
     *         instance.
     *
     * @todo Should somewhat return the content-type as well; this information
     *       is not disclosed by OneDrive.
     */
    public function fetchContent(array $options = [])
    {
        return $this->_client->apiGet($this->_id . '/content', $options);
    }

    /**
     * Copies the OneDrive file referenced by this File instance into another
     * OneDrive folder.
     *
     * @param null|string $destinationId
     *        The unique ID of the OneDrive folder into which to copy the
     *        OneDrive file referenced by this File instance, or null to copy it
     *        in the OneDrive root folder. Default: null.
     */
    public function copy($destinationId = null)
    {
        $this->_client->copyFile($this->_id, $destinationId);
    }
}
