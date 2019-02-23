<?php

namespace Krizalys\Onedrive\Proxy;

use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Stream;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\DriveItem;
use Microsoft\Graph\Model\DriveItemVersion;
use Microsoft\Graph\Model\Permission;
use Microsoft\Graph\Model\Thumbnail;

class DriveItemProxy extends BaseItemProxy
{
    /**
     * Constructor.
     *
     * @param Graph
     *        The graph.
     * @param DriveItem
     *        The drive item.
     */
    public function __construct(Graph $graph, DriveItem $driveItem)
    {
        parent::__construct($graph, $driveItem);
    }

    /**
     * Getter.
     *
     * @param string $name
     *        The name.
     *
     * @return mixed
     *         The value.
     */
    public function __get($name)
    {
        $driveItem = $this->entity;

        switch ($name) {
            case 'audio':
                $audio = $driveItem->getAudio();
                return $audio !== null ? new AudioProxy($this->graph, $audio) : null;

            case 'content':
                return $this->download();

            case 'cTag':
                return $driveItem->getCTag();

            case 'deleted':
                $deleted = $driveItem->getDeleted();
                return $deleted !== null ? new DeletedProxy($this->graph, $deleted) : null;

            case 'file':
                $file = $driveItem->getFile();
                return $file !== null ? new FileProxy($this->graph, $file) : null;

            case 'fileSystemInfo':
                $fileSystemInfo = $driveItem->getFileSystemInfo();
                return $fileSystemInfo !== null ? new FileSystemInfoProxy($this->graph, $fileSystemInfo) : null;

            case 'folder':
                $folder = $driveItem->getFolder();
                return $folder !== null ? new FolderProxy($this->graph, $folder) : null;

            case 'image':
                $image = $driveItem->getImage();
                return $image !== null ? new ImageProxy($this->graph, $image) : null;

            case 'location':
                $location = $driveItem->getLocation();
                return $location !== null ? new GeoCoordinatesProxy($this->graph, $location) : null;

            case 'package':
                $package = $driveItem->getPackage();
                return $package !== null ? new PackageProxy($this->graph, $package) : null;

            case 'photo':
                $photo = $driveItem->getPhoto();
                return $photo !== null ? new PhotoProxy($this->graph, $photo) : null;

            case 'publication':
                $publication = $driveItem->getPublication();
                return $publication !== null ? new PublicationFacetProxy($this->graph, $publication) : null;

            case 'remoteItem':
                $remoteItem = $driveItem->getRemoteItem();
                return $remoteItem !== null ? new RemoteItemProxy($this->graph, $remoteItem) : null;

            case 'root':
                $root = $driveItem->getRoot();
                return $root !== null ? new RootProxy($this->graph, $root) : null;

            case 'searchResult':
                $searchResult = $driveItem->getSearchResult();
                return $searchResult !== null ? new SearchResultProxy($this->graph, $searchResult) : null;

            case 'shared':
                $shared = $driveItem->getShared();
                return $shared !== null ? new SharedProxy($this->graph, $shared) : null;

            case 'sharepointIds':
                $sharepointIds = $driveItem->getSharepointIds();
                return $sharepointIds !== null ? new SharepointIdsProxy($this->graph, $sharepointIds) : null;

            case 'size':
                return $driveItem->getSize();

            case 'specialFolder':
                $specialFolder = $driveItem->getSpecialFolder();
                return $specialFolder !== null ? new SpecialFolderProxy($this->graph, $specialFolder) : null;

            case 'video':
                $video = $driveItem->getVideo();
                return $video !== null ? new VideoProxy($this->graph, $video) : null;

            case 'webDavUrl':
                return $driveItem->getWebDavUrl();

            case 'children':
                return $this->getChildren();

            case 'listItem':
                $listItem = $driveItem->getListItem();
                return $listItem !== null ? new ListItemProxy($this->graph, $listItem) : null;

            case 'permissions':
                $permissions = $driveItem->getPermissions();

                return $permissions !== null ? array_map(function (Permission $permission) {
                    return new PermissionProxy($this->graph, $permission);
                }, $permissions) : null;

            case 'thumbnails':
                $thumbnails = $driveItem->getThumbnails();

                return $thumbnails !== null ? array_map(function (Thumbnail $thumbnail) {
                    return new ThumbnailProxy($this->graph, $thumbnail);
                }, $thumbnails) : null;

            case 'versions':
                $versions = $driveItem->getVersions();

                return $versions !== null ? array_map(function (DriveItemVersion $driveItemVersion) {
                    return new DriveItemVersionProxy($this->graph, $driveItemVersion);
                }, $versions) : null;

            case 'workbook':
                $workbook = $driveItem->getWorkbook();
                return $workbook !== null ? new WorkbookProxy($this->graph, $workbook) : null;

            default:
                return parent::__get($name);
        }
    }

    /**
     * Creates a folder under this folder drive item.
     *
     * @param string $name
     *        The name.
     * @param array $options
     *        The options.
     *
     * @return DriveItemProxy
     *         The drive item created.
     *
     * @todo Support name conflict behavior.
     */
    public function createFolder($name, array $options = [])
    {
        $driveLocator = "/drives/{$this->parentReference->driveId}";
        $itemLocator  = "/items/{$this->id}";
        $endpoint     = "$driveLocator$itemLocator/children";

        $body = [
            'folder' => [
                '@odata.type' => 'microsoft.graph.folder',
            ],
            'name' => (string) $name,
        ];

        $response = $this
            ->graph
            ->createRequest('POST', $endpoint)
            ->attachBody($body + $options)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200 && $status != 201) {
            throw new \Exception("Unexpected status code produced by 'POST $endpoint': $status");
        }

        $driveItem = $response->getResponseAsObject(DriveItem::class);

        return new self($this->graph, $driveItem);
    }

    /**
     * Gets this folder drive item's children.
     *
     * @return array
     *         The child drive items.
     *
     * @todo Support pagination using a native iterator.
     */
    public function getChildren()
    {
        $driveLocator = "/drives/{$this->parentReference->driveId}";
        $itemLocator  = "/items/{$this->id}";
        $endpoint     = "$driveLocator$itemLocator/children";

        $response = $this
            ->graph
            ->createCollectionRequest('GET', $endpoint)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception("Unexpected status code produced by 'GET $endpoint': $status");
        }

        $driveItems = $response->getResponseAsObject(DriveItem::class);

        if (!is_array($driveItems)) {
            return [];
        }

        return array_map(function (DriveItem $driveItem) {
            return new self($this->graph, $driveItem);
        }, $driveItems);
    }

    /**
     * Deletes this drive item.
     */
    public function delete()
    {
        $driveLocator = "/drives/{$this->parentReference->driveId}";
        $itemLocator  = "/items/{$this->id}";
        $endpoint     = "$driveLocator$itemLocator";

        $response = $this
            ->graph
            ->createRequest('DELETE', $endpoint)
            ->execute();

        $status = $response->getStatus();

        if ($status != 204) {
            throw new \Exception("Unexpected status code produced by 'DELETE $endpoint': $status");
        }
    }

    /**
     * Uploads a file under this folder drive item.
     *
     * @param string $name
     *        The name.
     * @param string|resource|\GuzzleHttp\Psr7\Stream $content
     *        The content.
     * @param array $options
     *        The options.
     *
     * @return DriveItemProxy
     *         The drive item created.
     *
     * @todo Support name conflict behavior.
     * @todo Support content type in options.
     */
    public function upload($name, $content, array $options = [])
    {
        $name         = rawurlencode($name);
        $driveLocator = "/drives/{$this->parentReference->driveId}";
        $itemLocator  = "/items/{$this->id}";
        $endpoint     = "$driveLocator$itemLocator:/$name:/content";

        $body = $content instanceof Stream ?
            $content
            : Psr7\stream_for($content);

        $response = $this
            ->graph
            ->createRequest('PUT', $endpoint)
            ->addHeaders($options)
            ->attachBody($body)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200 && $status != 201) {
            throw new \Exception("Unexpected status code produced by 'PUT $endpoint': $status");
        }

        $driveItem = $response->getResponseAsObject(DriveItem::class);

        return new self($this->graph, $driveItem);
    }

    /**
     * Downloads this file drive item.
     *
     * @return GuzzleHttp\Psr7\Stream
     *         The content.
     */
    public function download()
    {
        $driveLocator = "/drives/{$this->parentReference->driveId}";
        $itemLocator  = "/items/{$this->id}";
        $endpoint     = "$driveLocator$itemLocator/content";

        $response = $this
            ->graph
            ->createRequest('GET', $endpoint)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception("Unexpected status code produced by 'GET $endpoint': $status");
        }

        return $response->getResponseAsObject(Stream::class);
    }

    /**
     * Renames this file item.
     *
     * @param string $name
     *        The name.
     * @param array $options
     *        The options.
     *
     * @return DriveItemProxy
     *         The drive item renamed.
     */
    public function rename($name, array $options = [])
    {
        $driveLocator = "/drives/{$this->parentReference->driveId}";
        $itemLocator  = "/items/{$this->id}";
        $endpoint     = "$driveLocator$itemLocator";

        $body = [
            'name' => (string) $name,
        ];

        $response = $this
            ->graph
            ->createRequest('PATCH', $endpoint)
            ->attachBody($body + $options)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception("Unexpected status code produced by 'PATCH $endpoint': $status");
        }

        $driveItem = $response->getResponseAsObject(DriveItem::class);

        return new self($this->graph, $driveItem);
    }

    /**
     * Moves this drive item.
     *
     * @param DriveItemProxy $destinationItem
     *        The destination item.
     * @param array $options
     *        The options.
     *
     * @return DriveItemProxy
     *         The drive item.
     */
    public function move(self $destinationItem, array $options = [])
    {
        $driveLocator = "/drives/{$this->parentReference->driveId}";
        $itemLocator  = "/items/{$this->id}";
        $endpoint     = "$driveLocator$itemLocator";

        $body = [
            'parentReference' => [
                'id' => $destinationItem->id,
            ],
        ];

        $response = $this
            ->graph
            ->createRequest('PATCH', $endpoint)
            ->attachBody($body + $options)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception("Unexpected status code produced by 'PATCH $endpoint': $status");
        }

        $driveItem = $response->getResponseAsObject(DriveItem::class);

        return new self($this->graph, $driveItem);
    }

    /**
     * Copies this drive item.
     *
     * @param DriveItemProxy $destinationItem
     *        The destination item.
     * @param array $options
     *        The options.
     *
     * @return string
     *         The progress URI.
     *
     * @todo Support asynchronous Graph operation.
     */
    public function copy(self $destinationItem, array $options = [])
    {
        $driveLocator = "/drives/{$this->parentReference->driveId}";
        $itemLocator  = "/items/{$this->id}";
        $endpoint     = "$driveLocator$itemLocator/copy";

        $body = [
            'parentReference' => [
                'id' => $destinationItem->id,
            ],
        ];

        $response = $this
            ->graph
            ->createRequest('POST', $endpoint)
            ->attachBody($body + $options)
            ->execute();

        $status = $response->getStatus();

        if ($status != 202) {
            throw new \Exception("Unexpected status code produced by 'POST $endpoint': $status");
        }

        $headers = $response->getHeaders();

        return $headers['Location'][0];
    }
}
