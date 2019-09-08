<?php

namespace Test\Functional\Krizalys\Onedrive\Traits;

use GuzzleHttp\Exception\ClientException;
use Krizalys\Onedrive\Constant\DriveType;
use Krizalys\Onedrive\Constant\FolderViewSortBy;
use Krizalys\Onedrive\Constant\FolderViewSortOrder;
use Krizalys\Onedrive\Constant\FolderViewType;
use Krizalys\Onedrive\Constant\QuotaStatus;
use Krizalys\Onedrive\Constant\SharingLinkScope;
use Krizalys\Onedrive\Constant\SharingLinkType;
use Krizalys\Onedrive\Proxy\AudioProxy;
use Krizalys\Onedrive\Proxy\BaseItemProxy;
use Krizalys\Onedrive\Proxy\DeletedProxy;
use Krizalys\Onedrive\Proxy\DriveItemProxy;
use Krizalys\Onedrive\Proxy\DriveProxy;
use Krizalys\Onedrive\Proxy\EntityProxy;
use Krizalys\Onedrive\Proxy\FileProxy;
use Krizalys\Onedrive\Proxy\FileSystemInfoProxy;
use Krizalys\Onedrive\Proxy\FolderProxy;
use Krizalys\Onedrive\Proxy\FolderViewProxy;
use Krizalys\Onedrive\Proxy\GeoCoordinatesProxy;
use Krizalys\Onedrive\Proxy\GraphListProxy;
use Krizalys\Onedrive\Proxy\HashesProxy;
use Krizalys\Onedrive\Proxy\IdentityProxy;
use Krizalys\Onedrive\Proxy\IdentitySetProxy;
use Krizalys\Onedrive\Proxy\ImageProxy;
use Krizalys\Onedrive\Proxy\ItemReferenceProxy;
use Krizalys\Onedrive\Proxy\ListItemProxy;
use Krizalys\Onedrive\Proxy\PackageProxy;
use Krizalys\Onedrive\Proxy\PermissionProxy;
use Krizalys\Onedrive\Proxy\PhotoProxy;
use Krizalys\Onedrive\Proxy\PublicationFacetProxy;
use Krizalys\Onedrive\Proxy\QuotaProxy;
use Krizalys\Onedrive\Proxy\RemoteItemProxy;
use Krizalys\Onedrive\Proxy\RootProxy;
use Krizalys\Onedrive\Proxy\SearchResultProxy;
use Krizalys\Onedrive\Proxy\SharedProxy;
use Krizalys\Onedrive\Proxy\SharepointIdsProxy;
use Krizalys\Onedrive\Proxy\SharingLinkProxy;
use Krizalys\Onedrive\Proxy\SpecialFolderProxy;
use Krizalys\Onedrive\Proxy\SystemProxy;
use Krizalys\Onedrive\Proxy\ThumbnailProxy;
use Krizalys\Onedrive\Proxy\UploadSessionProxy;
use Krizalys\Onedrive\Proxy\UserProxy;
use Krizalys\Onedrive\Proxy\VideoProxy;
use Krizalys\Onedrive\Proxy\WorkbookProxy;

trait AssertionsTrait
{
    private static $uriRegex = '|^([^:/?#]+:)?(//[^/?#]*)?[^?#]*(\?[^#]*)?(#.*)?|';

    private function assertBaseItemProxy($baseItem)
    {
        $this->assertEntityProxy($baseItem);
        $this->assertInstanceOf(BaseItemProxy::class, $baseItem);

        if ($baseItem->createdBy !== null) {
            $this->assertIdentitySetProxy($baseItem->createdBy);
        }

        $this->assertThat(
            $baseItem->createdDateTime,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(\DateTime::class)
            )
        );

        $this->assertThat(
            $baseItem->description,
            $this->logicalOr(
                $this->isNull(),
                $this->isType('string')
            )
        );

        $this->assertThat(
            $baseItem->eTag,
            $this->logicalOr(
                $this->isNull(),
                $this->isType('string')
            )
        );

        if ($baseItem->createdBy !== null) {
            $this->assertIdentitySetProxy($baseItem->createdBy);
        }

        $this->assertThat(
            $baseItem->lastModifiedDateTime,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(\DateTime::class)
            )
        );

        $this->assertThat(
            $baseName->name,
            $this->logicalOr(
                $this->isNull(),
                $this->isType('string')
            )
        );

        if ($baseItem->parentReference !== null) {
            $this->assertItemReferenceProxy($baseItem->parentReference);
        }

        $this->assertThat(
            $baseItem->webUrl,
            $this->logicalOr(
                $this->isNull(),
                $this->matchesRegularExpression(self::$uriRegex)
            )
        );

        $this->assertThat(
            $baseItem->createdByUser,
            $this->logicalOr(
                $this->isNull,
                $this->isInstanceOf(UserProxy::class)
            )
        );

        $this->assertThat(
            $baseItem->lastModifiedByUser,
            $this->logicalOr(
                $this->isNull,
                $this->isInstanceOf(UserProxy::class)
            )
        );
    }

    private function assertDriveItemProxy($item)
    {
        $this->assertBaseItemProxy($item);
        $this->assertInstanceOf(DriveItemProxy::class, $item);

        $this->assertThat(
            $item->audio,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(AudioProxy::class)
            )
        );

        //"content" => [ "@odata.type" => "Edm.Stream" ],
        $this->assertNotNull($item->cTag);

        $this->assertThat(
            $item->deleted,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(DeletedProxy::class)
            )
        );

        $this->assertThat(
            $item->file,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(FileProxy::class)
            )
        );

        $this->assertThat(
            $item->fileSystemInfo,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(FileSystemInfoProxy::class)
            )
        );

        $this->assertThat(
            $item->folder,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(FolderProxy::class)
            )
        );

        $this->assertThat(
            $item->image,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(ImageProxy::class)
            )
        );

        $this->assertThat(
            $item->location,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(GeoCoordinatesProxy::class)
            )
        );

        $this->assertThat(
            $item->package,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(PackageProxy::class)
            )
        );

        $this->assertThat(
            $item->photo,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(PhotoProxy::class)
            )
        );

        $this->assertThat(
            $item->publication,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(PublicationFacetProxy::class)
            )
        );

        $this->assertThat(
            $item->remoteItem,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(RemoteItemProxy::class)
            )
        );

        $this->assertThat(
            $item->root,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(RootProxy::class)
            )
        );

        $this->assertThat(
            $item->searchResult,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(SearchResultProxy::class)
            )
        );

        $this->assertThat(
            $item->shared,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(SharedProxy::class)
            )
        );

        $this->assertThat(
            $item->sharepointIds,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(SharepointIdsProxy::class)
            )
        );

        $this->assertThat(
            $item->size,
            $this->logicalOr(
                $this->isNull(),
                $this->greaterThanOrEqual(0)
            )
        );

        $this->assertThat(
            $item->specialFolder,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(SpecialFolderProxy::class)
            )
        );

        $this->assertThat(
            $item->video,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(VideoProxy::class)
            )
        );

        $this->assertThat(
            $item->webDavUrl,
            $this->logicalOr(
                $this->isNull(),
                $this->matchesRegularExpression(self::$uriRegex)
            )
        );

        foreach ($item->children as $child) {
            // Assert safely to prevent transient items created by parallel
            // processes from not being found anymore.
            try {
                $this->assertDriveItemProxy($child);
            } catch (ClientException $exception) {
                $statusCode = $exception
                    ->getResponse()
                    ->getStatusCode();

                if ($statusCode != 404) {
                    throw $exception;
                }
            }
        }

        $this->assertThat(
            $item->listItem,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(ListItemProxy::class)
            )
        );

        if ($item->permissions !== null) {
            foreach ($item->permissions as $permission) {
                $this->assertPermissionProxy($permission);
            }
        }

        if ($item->thumbnails !== null) {
            foreach ($item->thumbnails as $thumbnail) {
                $this->assertThumbnailProxy($thumbnail);
            }
        }

        if ($item->versions !== null) {
            foreach ($item->versions as $version) {
                $this->assertInternalType('string', $version);
            }
        }

        $this->assertThat(
            $item->workbook,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(WorkbookProxy::class)
            )
        );
    }

    private function assertDriveProxy($drive)
    {
        $this->assertBaseItemProxy($drive);
        $this->assertInstanceOf(DriveProxy::class, $drive);

        $this->assertContains($drive->driveType, [
            DriveType::PERSONAL,
            DriveType::BUSINESS,
            DriveType::DOCUMENT_LIBRARY,
        ]);

        $this->assertIdentitySetProxy($drive->owner);
        $this->assertQuotaProxy($drive->quota);

        $this->assertThat(
            $drive->sharePointIds,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(SharepointIdsProxy::class)
            )
        );

        $this->assertThat(
            $drive->system,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(SystemProxy::class)
            )
        );

        if ($drive->items !== null) {
            foreach ($drive->items as $item) {
                $this->assertDriveItemProxy($item);
            }
        }

        $this->assertThat(
            $drive->list,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(GraphListProxy::class)
            )
        );

        $this->assertThat(
            $drive->root,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(DriveItemProxy::class)
            )
        );

        $this->assertThat(
            $drive->special,
            $this->logicalOr(
                $this->isNull(),
                $this->isInstanceOf(DriveItemProxy::class)
            )
        );
    }

    private function assertEntityProxy($entity)
    {
        $this->assertInstanceOf(EntityProxy::class, $entity);

        $this->assertThat(
            $entity->id,
            $this->logicalOr(
                $this->isNull(),
                $this->isType('string')
            )
        );
    }

    private function assertFileProxy($file)
    {
        $this->assertEntityProxy($file);
        $this->assertInstanceOf(FileProxy::class, $file);
        $this->assertHashesProxy($file->hashes);
        $this->assertInternalType('string', $file->mimeType);
    }

    private function assertFolderViewProxy($folderView)
    {
        $this->assertEntityProxy($folderView);
        $this->assertInstanceOf(FolderViewProxy::class, $folderView);
        $this->assertInternalType('string', $folderView->sortBy);

        $this->assertContains($folderView->sortBy, [
            FolderViewSortBy::DEFAULT_,
            FolderViewSortBy::NAME,
            FolderViewSortBy::TYPE,
            FolderViewSortBy::SIZE,
            FolderViewSortBy::TAKEN_OR_CREATED_DATE_TIME,
            FolderViewSortBy::LAST_MODIFIED_DATE_TIME,
            FolderViewSortBy::SEQUENCE,
        ]);

        $this->assertInternalType('string', $folderView->sortOrder);

        $this->assertContains($folderView->sortOrder, [
            FolderViewSortOrder::ASCENDING,
            FolderViewSortOrder::DESCENDING,
        ]);

        $this->assertInternalType('string', $folderView->viewType);

        $this->assertContains($folderView->viewType, [
            FolderViewType::DEFAULT_,
            FolderViewType::ICONS,
            FolderViewType::DETAILS,
            FolderViewType::THUMBNAILS,
        ]);
    }

    private function assertHashesProxy($hashes)
    {
        $this->assertEntityProxy($hashes);
        $this->assertInstanceOf(HashesProxy::class, $hashes);

        $this->assertThat(
            $hashes->crc32Hash,
            $this->logicalOr(
                $this->isNull(),
                $this->isType('string')
            )
        );

        $this->assertInternalType('string', $hashes->quickXorHash);
        $this->assertInternalType('string', $hashes->sha1Hash);
    }

    private function assertIdentityProxy($identity)
    {
        $this->assertEntityProxy($identity);
        $this->assertInstanceOf(IdentityProxy::class, $identity);

        $this->assertThat(
            $identity->displayName,
            $this->logicalOr(
                $this->isNull(),
                $this->logicalAnd(
                    $this->isType('string'),
                    $this->logicalNot($this->equalTo(''))
                )
            )
        );
    }

    private function assertIdentitySetProxy($identitySet)
    {
        $this->assertEntityProxy($identitySet);
        $this->assertInstanceOf(IdentitySetProxy::class, $identitySet);

        if ($identitySet->application !== null) {
            $this->assertIdentityProxy($identitySet->application);
        }

        if ($identitySet->device !== null) {
            $this->assertIdentityProxy($identitySet->device);
        }

        $this->assertIdentityProxy($identitySet->user);
    }

    private function assertItemReferenceProxy($itemReference)
    {
        $this->assertEntityProxy($itemReference);
        $this->assertInstanceOf(ItemReferenceProxy::class, $itemReference);

        $this->assertThat(
            $itemReference->id,
            $this->logicalOr(
                $this->isNull(),
                $this->isType('string')
            )
        );

        $this->assertInternalType('string', $itemReference->driveId);
        $this->assertInternalType('string', $itemReference->driveType);

        $this->assertContains($itemReference->driveType, [
            DriveType::PERSONAL,
            DriveType::BUSINESS,
            DriveType::DOCUMENT_LIBRARY,
        ]);

        $this->assertThat(
            $itemReference->path,
            $this->logicalOr(
                $this->isNull(),
                $this->isType('string')
            )
        );
    }

    private function assertPermissionProxy($permission)
    {
        $this->assertEntityProxy($permission);
        $this->assertInstanceOf(PermissionProxy::class, $permission);
        $this->assertSharingLinkProxy($permission->link);
    }

    private function assertQuotaProxy($quota)
    {
        $this->assertEntityProxy($quota);
        $this->assertInstanceOf(QuotaProxy::class, $quota);
        $this->assertGreaterThanOrEqual(0, $quota->deleted);
        $this->assertGreaterThanOrEqual(0, $quota->remaining);

        $this->assertContains($quota->state, [
            QuotaStatus::NORMAL,
            QuotaStatus::NEARING,
            QuotaStatus::CRITICAL,
            QuotaStatus::EXCEEDED,
        ]);

        $this->assertGreaterThanOrEqual(0, $quota->total);
        $this->assertGreaterThanOrEqual(0, $quota->used);
    }

    private function assertRootProxy($root)
    {
        $this->assertEntityProxy($root);
        $this->assertInstanceOf(RootProxy::class, $root);
    }

    private function assertSpecialFolderProxy($specialFolder)
    {
        $this->assertEntityProxy($specialFolder);
        $this->assertInstanceOf(SpecialFolderProxy::class, $specialFolder);
        $this->assertInternalType('string', $specialFolder->name);
    }

    private function assertSharingLinkProxy($sharingLink)
    {
        $this->assertEntityProxy($sharingLink);
        $this->assertInstanceOf(SharingLinkProxy::class, $sharingLink);

        if ($sharingLink->application !== null) {
            $this->assertIdentityProxy($sharingLink->application);
        }

        if ($sharingLink->scope !== null) {
            $this->assertContains($sharingLink->scope, [
                SharingLinkScope::ANONYMOUS,
                SharingLinkScope::ORGANIZATION,
            ]);
        }

        if ($sharingLink->type !== null) {
            $this->assertContains($sharingLink->type, [
                SharingLinkType::VIEW,
                SharingLinkType::EDIT,
                SharingLinkType::EMBED,
            ]);
        }

        $this->assertRegExp(self::$uriRegex, $sharingLink->webUrl);
    }

    private function assertThumbnailProxy($thumbnail)
    {
        $this->assertEntityProxy($thumbnail);
        $this->assertInstanceOf(ThumbnailProxy::class, $thumbnail);
    }

    private function assertUploadSessionProxy($uploadSession)
    {
        $this->assertEntityProxy($uploadSession);
        $this->assertInstanceOf(UploadSessionProxy::class, $uploadSession);
        $this->assertInstanceOf(\DateTime::class, $uploadSession->expirationDateTime);
        $this->assertCount(1, $uploadSession->nextExpectedRanges);
        $this->assertEquals('0-', $uploadSession->nextExpectedRanges[0]);
        $this->assertRegExp(self::$uriRegex, $uploadSession->uploadUrl);
    }
}
