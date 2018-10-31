<?php

namespace Krizalys\Onedrive;

use Krizalys\Onedrive\Proxy\DriveItemProxy;
use Psr\Log\LogLevel;

/**
 * @class Folder
 *
 * A Folder instance is a DriveItem instance referencing to a OneDrive folder.
 * It may contain other OneDrive drive items but may not have content.
 *
 * @deprecated Use Krizalys\Onedrive\Proxy\DriveItemProxy and/or
 *             Krizalys\Onedrive\Proxy\Folder instead.
 */
class Folder extends DriveItem
{
    /**
     * {@inheritdoc}
     */
    public function isFolder()
    {
        return true;
    }

    /**
     * Constructor.
     *
     * @param Client $client
     *        The Client instance owning this DriveItem instance.
     * @param null|string $id
     *        The unique ID of the OneDrive drive item referenced by this
     *        DriveItem instance, or null to reference the OneDrive root folder.
     *        Default: null.
     * @param array|object $options
     *        Options to pass to the DriveItem constructor.
     */
    public function __construct(Client $client, $id = null, $options = [])
    {
        parent::__construct($client, $id, $options);
    }

    /**
     * Gets the drive items in the OneDrive folder referenced by this Folder
     * instance.
     *
     * @return array
     *         The drive items in the OneDrive folder referenced by this Folder
     *         instance, as DriveItem instances.
     *
     * @deprecated Use Krizalys\Onedrive\Proxy\DriveItemProxy::children instead.
     */
    public function fetchDriveItems()
    {
        $client = $this->_client;

        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Proxy\DriveItemProxy::children'
                . ' instead',
            __METHOD__
        );

        $client->log(LogLevel::WARNING, $message);
        $drive = $client->getMyDrive();
        $item  = $client->getDriveItemById($drive->id, $this->_id);

        return array_map(function (DriveItemProxy $item) use ($client) {
            $options = $client->buildOptions($item, ['parent_id' => $this->_id]);

            return $client->isFolder($item) ?
                new self($client, $item->id, $options)
                : new File($client, $item->id, $options);
        }, $item->children);
    }

    /**
     * Gets the child drive items in the OneDrive folder referenced by this
     * Folder instance.
     *
     * @return array
     *         The drive items in the OneDrive folder referenced by this Folder
     *         instance, as DriveItem instances.
     *
     * @deprecated Use Krizalys\Onedrive\Proxy\DriveItemProxy::children instead.
     */
    public function fetchChildDriveItems()
    {
        $client = $this->_client;

        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Proxy\DriveItemProxy::children'
                . ' instead',
            __METHOD__
        );

        $client->log(LogLevel::WARNING, $message);
        $drive = $client->getMyDrive();
        $item  = $client->getDriveItemById($drive->id, $this->_id);

        return array_map(function (DriveItemProxy $item) use ($client) {
            $options = $client->buildOptions($item, ['parent_id' => $this->_id]);

            return $client->isFolder($item) ?
                new self($client, $item->id, $options)
                : new File($client, $item->id, $options);
        }, $item->children);
    }

    /**
     * Creates a folder in the OneDrive folder referenced by this Folder
     * instance.
     *
     * @param string $name
     *        The name of the OneDrive folder to be created.
     * @param null|string $description
     *        The description of the OneDrive folder to be created, or null to
     *        create it without a description. Default: null.
     *
     * @return Folder
     *         The folder created, as a Folder instance.
     *
     * @deprecated Use Krizalys\Onedrive\Proxy\DriveItemProxy::createFolder()
     *             instead.
     */
    public function createFolder($name, $description = null)
    {
        $client = $this->_client;

        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Proxy\DriveItemProxy::createFolder()'
                . ' instead',
            __METHOD__
        );

        $client->log(LogLevel::WARNING, $message);
        $drive   = $client->getMyDrive();
        $item    = $client->getDriveItemById($drive->id, $this->_id);
        $options = [];

        if ($description !== null) {
            $options += [
                'description' => (string) $description,
            ];
        }

        $item    = $item->createFolder($name, $options);
        $options = $client->buildOptions($item, ['parent_id' => $parentId]);

        return new self($client, $item->id, $options);
    }

    /**
     * Creates a file in the OneDrive folder referenced by this Folder instance.
     *
     * @param string $name
     *        The name of the OneDrive file to be created.
     * @param string|resource $content
     *        The content of the OneDrive file to be created, as a string or
     *        handle to an already opened file. In the latter case, the
     *        responsibility to close the handle is is left to the calling
     *        function. Default: ''.
     * @param array $options
     *        The options.
     *
     * @return File
     *         The file created, as a File instance.
     *
     * @throws Exception
     *         Thrown on I/O errors.
     *
     * @deprecated Use Krizalys\Onedrive\Proxy\DriveItemProxy::upload() instead.
     */
    public function createFile($name, $content = '', array $options = [])
    {
        $client = $this->_client;

        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Proxy\DriveItemProxy::upload()'
                . ' instead',
            __METHOD__
        );

        $client->log(LogLevel::WARNING, $message);
        $drive   = $client->getMyDrive();
        $item    = $client->getDriveItemById($drive->id, $this->_id);
        $options = [];

        if ($description !== null) {
            $options += [
                'description' => (string) $description,
            ];
        }

        $item    = $item->upload($name, $content, $options);
        $options = $client->buildOptions($item, ['parent_id' => $parentId]);

        return new File($client, $item->id, $options);
    }
}
