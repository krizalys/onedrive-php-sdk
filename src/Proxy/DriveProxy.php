<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Drive;
use Microsoft\Graph\Model\DriveItem;

class DriveProxy extends BaseItemProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param Drive $drive
     *        The drive.
     */
    public function __construct(Graph $graph, Drive $drive)
    {
        parent::__construct($graph, $drive);
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
        $drive = $this->entity;

        switch ($name) {
            case 'driveType':
                return $drive->getDriveType();

            case 'owner':
                $owner = $drive->getOwner();
                return $owner !== null ? new IdentitySetProxy($this->graph, $owner) : null;

            case 'quota':
                $quota = $drive->getQuota();
                return $quota !== null ? new QuotaProxy($this->graph, $quota) : null;

            case 'sharePointIds':
                $sharePointIds = $drive->getSharePointIds();
                return $sharePointIds !== null ? new SharepointIdsProxy($this->graph, $sharePointIds) : null;

            case 'system':
                $system = $drive->getSystem();
                return $system !== null ? new SystemFacetProxy($this->graph, $system) : null;

            case 'items':
                $items = $drive->getItems();

                return $items !== null ? array_map(function (DriveItem $item) {
                    return new DriveItemProxy($this->graph, $item);
                }, $items) : null;

            case 'list':
                $list = $drive->getList();
                return $list !== null ? new GraphListProxy($this->graph, $list) : null;

            case 'root':
                $root = $drive->getRoot();
                return $root !== null ? new DriveItemProxy($this->graph, $root) : null;

            case 'special':
                $special = $drive->getSpecial();
                return $special !== null ? new DriveItemProxy($this->graph, $special) : null;

            default:
                return parent::__get($name);
        }
    }

    /**
     * @return DriveItemProxy
     *         The root.
     */
    public function getRoot()
    {
        $driveLocator = "/drives/{$this->id}";
        $itemLocator  = '/items/root';
        $endpoint     = "$driveLocator$itemLocator";

        $response = $this
            ->graph
            ->createRequest('GET', $endpoint)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception("Unexpected status code produced by 'GET $endpoint': $status");
        }

        $driveItem = $response->getResponseAsObject(DriveItem::class);

        return new DriveItemProxy($this->graph, $driveItem);
    }
}
