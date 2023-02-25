<?php

/**
 * This file is part of Krizalys' OneDrive SDK for PHP.
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @author    Christophe Vidal
 * @copyright 2008-2023 Christophe Vidal (http://www.krizalys.com)
 * @license   https://opensource.org/licenses/BSD-3-Clause 3-Clause BSD License
 * @link      https://github.com/krizalys/onedrive-php-sdk
 */

declare(strict_types=1);

namespace Krizalys\Onedrive;

use GuzzleHttp\ClientInterface;
use Krizalys\Onedrive\Constant\AccessTokenStatus;
use Krizalys\Onedrive\Constant\SpecialFolderName;
use Krizalys\Onedrive\Definition\ServiceDefinitionInterface;
use Krizalys\Onedrive\Proxy\DriveItemProxy;
use Krizalys\Onedrive\Proxy\DriveProxy;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

/**
 * A client interface to communicate with the OneDrive API.
 *
 * `Client` instances act as entry points allowing client applications to
 * perform currently implemented OneDrive operations programmatically.
 *
 * For example, assuming you have instantiated a client and the user has logged
 * successfully and authorized your client application:
 *
 * ```php
 * $client->getRoot()->upload('hello.txt', 'Hello World!');
 * ```
 *
 * Applications are managed via the Microsoft identity platform (v2.0); see
 * {@link https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade "App registrations" in Microsoft Azure}.
 *
 * @since 1.0.0
 *
 * @api
 */
class Client
{
    /**
     * @var string
     *      The base URL for authorization requests.
     */
    const AUTH_URL = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';

    /**
     * @var string
     *      The base URL for token requests.
     */
    const TOKEN_URL = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';

    /**
     * @var string
     *      The client ID.
     */
    private $clientId;

    /**
     * @var \Microsoft\Graph\Graph
     *      The Microsoft Graph.
     */
    private $graph;

    /**
     * @var \GuzzleHttp\ClientInterface
     *      The Guzzle HTTP client.
     */
    private $httpClient;

    /**
     * @var \Krizalys\Onedrive\Definition\ServiceDefinitionInterface
     *      The service definition.
     */
    private $serviceDefinition;

    /**
     * @var \Krizalys\Onedrive\ClientState
     *      The OAuth state (token, etc...).
     */
    private $state;

    /**
     * Constructor.
     *
     * @param string $clientId
     *        The client ID.
     * @param \Microsoft\Graph\Graph $graph
     *        The Microsoft Graph.
     * @param \GuzzleHttp\ClientInterface $httpClient
     *        The Guzzle HTTP client.
     * @param \Krizalys\Onedrive\Definition\ServiceDefinitionInterface $serviceDefinition
     *        The service definition.
     * @param mixed[string] $options
     *        The options to use while creating this object. Supported options:
     *          - `'state'` *(\Krizalys\Onedrive\ClientState)*: the OneDrive
     *            client state, as returned by `getState()`. Default: `new
     *            ClientState()`.
     *
     * @throws \Exception
     *         Thrown if `$clientId` is `null`.
     *
     * @since 1.0.0
     */
    public function __construct(
        $clientId,
        Graph $graph,
        ClientInterface $httpClient,
        ServiceDefinitionInterface $serviceDefinition,
        array $options = []
    ) {
        if ($clientId === null) {
            throw new \Exception('The client ID must be set');
        }

        $this->clientId          = $clientId;
        $this->graph             = $graph;
        $this->httpClient        = $httpClient;
        $this->serviceDefinition = $serviceDefinition;

        $this->state = array_key_exists('state', $options)
            ? $options['state'] : new ClientState();

        if ($this->state->token !== null) {
            $this->graph->setAccessToken($this->state->token->data->access_token);
        }
    }

    /**
     * Gets the current state of this Client instance.
     *
     * Typically saved in the session and passed back to the `Client`
     * constructor for further requests.
     *
     * @return \Krizalys\Onedrive\ClientState
     *         The state of this `Client` instance.
     *
     * @since 1.0.0
     *
     * @api
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Gets the URL of the log in form.
     *
     * Users should visit this URL in their browser to first be presented a form
     * where the user is first allowed to log in to their OneDrive account, and
     * then to grant the requested permissions to the OneDrive application.
     *
     * After login, the browser is redirected to the given redirect URI, and a
     * code is passed as a query string parameter to this URI.
     *
     * A user-defined value may also be passed back to this URI as a query
     * string parameter via the `$state` parameter; see
     * {@link https://docs.microsoft.com/en-us/azure/active-directory/develop/reply-url#use-a-state-parameter "Use a state parameter" in Microsoft Azure documentation}
     * for example use
     * cases.
     *
     * The browser is also directly redirected to the given redirect URI if the
     * user is already logged in.
     *
     * @param string[] $scopes
     *        The OneDrive scopes requested by the application. Supported
     *        values:
     *          - `'offline_access'` ;
     *          - `'files.read'` ;
     *          - `'files.read.all'` ;
     *          - `'files.readwrite'` ;
     *          - `'files.readwrite.all'`.
     * @param string $redirectUri
     *        The URI to which to redirect to upon successful log in.
     * @param string $state
     *        The state to pass as a query string value to the redirect URI
     *        upon successful log in.
     *
     * @return string
     *         The log in URL.
     *
     * @since 1.0.0
     *
     * @api
     */
    public function getLogInUrl(array $scopes, $redirectUri, $state = null)
    {
        $redirectUri              = (string) $redirectUri;
        $this->state->redirectUri = $redirectUri;

        $values = [
            'client_id'     => $this->clientId,
            'response_type' => 'code',
            'redirect_uri'  => $redirectUri,
            'scope'         => implode(' ', $scopes),
            'response_mode' => 'query',
        ];

        if ($state !== null) {
            $values['state'] = (string) $state;
        }

        $query = http_build_query($values, '', '&', PHP_QUERY_RFC3986);

        // When visiting this URL and authenticating successfully, the agent is
        // redirected to the redirect URI, with a code passed in the query
        // string (the name of the variable is "code"). This is suitable for
        // PHP.
        return self::AUTH_URL . "?$query";
    }

    /**
     * Gets the access token expiration delay.
     *
     * @return int
     *         The token expiration delay, in seconds.
     *
     * @since 1.0.0
     *
     * @api
     */
    public function getTokenExpire()
    {
        return $this->state->token->obtained
            + $this->state->token->data->expires_in - time();
    }

    /**
     * Gets the status of the current access token.
     *
     * See {@see \Krizalys\Onedrive\Constant\AccessTokenStatus
     * AccessTokenStatus} for the possible values returned.
     *
     * @return int
     *         The status of the current access token.
     *
     * @since 1.0.0
     *
     * @api
     */
    public function getAccessTokenStatus()
    {
        if ($this->state->token === null) {
            return AccessTokenStatus::MISSING;
        }

        $remaining = $this->getTokenExpire();

        if ($remaining <= 0) {
            return AccessTokenStatus::EXPIRED;
        }

        if ($remaining <= 60) {
            return AccessTokenStatus::EXPIRING;
        }

        return AccessTokenStatus::VALID;
    }

    /**
     * Obtains a new access token from OAuth.
     *
     * This token is valid for one hour.
     *
     * @param string $clientSecret
     *        The OneDrive client secret.
     * @param string $code
     *        The code returned by OneDrive after successful log in.
     *
     * @throws \Exception
     *         Thrown if the redirect URI of this `Client` instance's state is
     *         not set.
     * @throws \Exception
     *         Thrown if the HTTP response body cannot be JSON-decoded.
     *
     * @since 1.0.0
     *
     * @api
     */
    public function obtainAccessToken($clientSecret, $code)
    {
        if ($this->state->redirectUri === null) {
            throw new \Exception(
                'The state\'s redirect URI must be set to call'
                    . ' obtainAccessToken()'
            );
        }

        $values = [
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->state->redirectUri,
            'client_secret' => (string) $clientSecret,
            'code'          => (string) $code,
            'grant_type'    => 'authorization_code',
        ];

        $response = $this->httpClient->post(
            self::TOKEN_URL,
            ['form_params' => $values]
        );

        $body = (string) $response->getBody();
        $data = json_decode($body);

        if ($data === null) {
            throw new \Exception('json_decode() failed');
        }

        $this->state->redirectUri = null;

        $this->state->token = (object) [
            'obtained' => time(),
            'data'     => $data,
        ];

        $this->graph->setAccessToken($this->state->token->data->access_token);
    }

    /**
     * Renews the access token from OAuth.
     *
     * This token is valid for one hour.
     *
     * @param string $clientSecret
     *        The client secret.
     *
     * @since 1.1.0
     *
     * @api
     */
    public function renewAccessToken($clientSecret)
    {
        if ($this->state->token->data->refresh_token === null) {
            throw new \Exception(
                'The refresh token is not set or no permission for'
                    . ' \'offline_access\' was given to renew the token'
            );
        }

        $values = [
            'client_id'     => $this->clientId,
            'client_secret' => $clientSecret,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->state->token->data->refresh_token,
        ];

        $response = $this->httpClient->post(
            self::TOKEN_URL,
            ['form_params' => $values]
        );

        $body = (string) $response->getBody();
        $data = json_decode($body);

        if ($data === null) {
            throw new \Exception('json_decode() failed');
        }

        $this->state->token = (object) [
            'obtained' => time(),
            'data'     => $data,
        ];

        $this->graph->setAccessToken($this->state->token->data->access_token);
    }

    /**
     * Gets the current user's drive.
     *
     * @return \Krizalys\Onedrive\Proxy\DriveProxy[]
     *         The drives.
     *
     * @since 2.0.0
     *
     * @api
     *
     * @link https://docs.microsoft.com/en-us/onedrive/developer/rest-api/api/drive_list?view=odsp-graph-online#list-the-current-users-drives
     *       List the current user's drives
     */
    public function getDrives()
    {
        $driveLocator = '/me/drives';
        $endpoint     = "$driveLocator";

        $response = $this
            ->graph
            ->createCollectionRequest('GET', $endpoint)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception("Unexpected status code produced by 'GET $endpoint': $status");
        }

        $drives = $response->getResponseAsObject(Model\Drive::class);

        if (!is_array($drives)) {
            return [];
        }

        return array_map(function (Model\Drive $drive) {
            return new DriveProxy(
                $this->graph,
                $drive,
                $this->serviceDefinition->getResourceDefinition('driveItem')
            );
        }, $drives);
    }

    /**
     * Gets the signed in user's drive.
     *
     * @return \Krizalys\Onedrive\Proxy\DriveProxy
     *         The drive.
     *
     * @since 2.0.0
     *
     * @api
     *
     * @link https://docs.microsoft.com/en-us/onedrive/developer/rest-api/api/drive_get?view=odsp-graph-online#get-current-users-onedrive
     *       Get current user's OneDrive
     */
    public function getMyDrive()
    {
        $driveLocator = '/me/drive';
        $endpoint     = "$driveLocator";

        $response = $this
            ->graph
            ->createRequest('GET', $endpoint)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception("Unexpected status code produced by 'GET $endpoint': $status");
        }

        $drive = $response->getResponseAsObject(Model\Drive::class);

        return new DriveProxy(
            $this->graph,
            $drive,
            $this->serviceDefinition->getResourceDefinition('driveItem')
        );
    }

    /**
     * Gets a drive by ID.
     *
     * @param string $driveId
     *        The drive ID.
     *
     * @return \Krizalys\Onedrive\Proxy\DriveProxy
     *         The drive.
     *
     * @since 2.0.0
     *
     * @api
     *
     * @link https://docs.microsoft.com/en-us/onedrive/developer/rest-api/api/drive_get?view=odsp-graph-online#get-a-drive-by-id
     *       Get a drive by ID
     */
    public function getDriveById($driveId)
    {
        $driveLocator = "/drives/$driveId";
        $endpoint     = "$driveLocator";

        $response = $this
            ->graph
            ->createRequest('GET', $endpoint)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception("Unexpected status code produced by 'GET $endpoint': $status");
        }

        $drive = $response->getResponseAsObject(Model\Drive::class);

        return new DriveProxy(
            $this->graph,
            $drive,
            $this->serviceDefinition->getResourceDefinition('driveItem')
        );
    }

    /**
     * Gets a user's OneDrive.
     *
     * @param string $idOrUserPrincipalName
     *        The ID or user principal name.
     *
     * @return \Krizalys\Onedrive\Proxy\DriveProxy
     *         The drive.
     *
     * @since 2.0.0
     *
     * @api
     *
     * @link https://docs.microsoft.com/en-us/onedrive/developer/rest-api/api/drive_get?view=odsp-graph-online#get-a-users-onedrive
     *       Get a user's OneDrive
     */
    public function getDriveByUser($idOrUserPrincipalName)
    {
        $userLocator  = "/users/$idOrUserPrincipalName";
        $driveLocator = '/drive';
        $endpoint     = "$userLocator$driveLocator";

        $response = $this
            ->graph
            ->createRequest('GET', $endpoint)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception("Unexpected status code produced by 'GET $endpoint': $status");
        }

        $drive = $response->getResponseAsObject(Model\Drive::class);

        return new DriveProxy(
            $this->graph,
            $drive,
            $this->serviceDefinition->getResourceDefinition('driveItem')
        );
    }

    /**
     * Gets the document library associated with a group.
     *
     * @param string $groupId
     *        The group ID.
     *
     * @return \Krizalys\Onedrive\Proxy\DriveProxy
     *         The drive.
     *
     * @since 2.0.0
     *
     * @api
     *
     * @link https://docs.microsoft.com/en-us/onedrive/developer/rest-api/api/drive_get?view=odsp-graph-online#get-the-document-library-associated-with-a-group
     *       Get the document library associated with a group
     */
    public function getDriveByGroup($groupId)
    {
        $groupLocator = "/groups/$groupId";
        $driveLocator = '/drive';
        $endpoint     = "$groupLocator$driveLocator";

        $response = $this
            ->graph
            ->createRequest('GET', $endpoint)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception("Unexpected status code produced by 'GET $endpoint': $status");
        }

        $drive = $response->getResponseAsObject(Model\Drive::class);

        return new DriveProxy(
            $this->graph,
            $drive,
            $this->serviceDefinition->getResourceDefinition('driveItem')
        );
    }

    /**
     * Gets the document library for a site.
     *
     * @param string $siteId
     *        The site ID.
     *
     * @return \Krizalys\Onedrive\Proxy\DriveProxy
     *         The drive.
     *
     * @since 2.0.0
     *
     * @api
     *
     * @link https://docs.microsoft.com/en-us/onedrive/developer/rest-api/api/drive_get?view=odsp-graph-online#get-the-document-library-for-a-site
     *       Get the document library for a site
     */
    public function getDriveBySite($siteId)
    {
        $siteLocator  = "/sites/$siteId";
        $driveLocator = '/drive';
        $endpoint     = "$siteLocator$driveLocator";

        $response = $this
            ->graph
            ->createRequest('GET', $endpoint)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception("Unexpected status code produced by 'GET $endpoint': $status");
        }

        $drive = $response->getResponseAsObject(Model\Drive::class);

        return new DriveProxy(
            $this->graph,
            $drive,
            $this->serviceDefinition->getResourceDefinition('driveItem')
        );
    }

    /**
     * Gets a drive item by ID.
     *
     * @param string $itemId
     *        The drive item ID.
     *
     * @return \Krizalys\Onedrive\Proxy\DriveItemProxy
     *         The drive item.
     *
     * @since 2.0.0
     *
     * @api
     *
     * @link https://docs.microsoft.com/en-us/onedrive/developer/rest-api/api/driveitem_get?view=odsp-graph-online
     *       Get a DriveItem resource
     */
    public function getDriveItemById($itemId)
    {
        $driveLocator = '/me/drive';
        $itemLocator  = "/items/$itemId";
        $endpoint     = "$driveLocator$itemLocator";

        $response = $this
            ->graph
            ->createRequest('GET', $endpoint)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception("Unexpected status code produced by 'GET $endpoint': $status");
        }

        $driveItem = $response->getResponseAsObject(Model\DriveItem::class);

        return new DriveItemProxy(
            $this->graph,
            $driveItem,
            $this->serviceDefinition->getResourceDefinition('driveItem')
        );
    }

    /**
     * Gets a drive item by path.
     *
     * The path is given as an absolute path from the root of the drive, for
     * example:
     *
     * ```php
     * $driveItem = $client->getDriveItemByPath('/path/to/file.txt');
     * ```
     *
     * @param string $path
     *        The path.
     *
     * @return \Krizalys\Onedrive\Proxy\DriveItemProxy
     *         The drive item.
     *
     * @since 2.2.0
     *
     * @api
     *
     * @link https://docs.microsoft.com/en-us/onedrive/developer/rest-api/api/driveitem_get?view=odsp-graph-online
     *       Get a DriveItem resource
     */
    public function getDriveItemByPath($path)
    {
        $driveLocator = '/me/drive';
        $itemLocator  = "/root:$path";
        $endpoint     = "$driveLocator$itemLocator";

        $response = $this
            ->graph
            ->createRequest('GET', $endpoint)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception("Unexpected status code produced by 'GET $endpoint': $status");
        }

        $driveItem = $response->getResponseAsObject(Model\DriveItem::class);

        return new DriveItemProxy(
            $this->graph,
            $driveItem,
            $this->serviceDefinition->getResourceDefinition('driveItem')
        );
    }

    /**
     * Gets the root drive item.
     *
     * @return \Krizalys\Onedrive\Proxy\DriveItemProxy
     *         The root drive item.
     *
     * @since 2.0.0
     *
     * @api
     *
     * @link https://docs.microsoft.com/en-us/onedrive/developer/rest-api/api/driveitem_get?view=odsp-graph-online
     *       Get a DriveItem resource
     */
    public function getRoot()
    {
        $driveLocator = '/me/drive';
        $itemLocator  = '/root';
        $endpoint     = "$driveLocator$itemLocator";

        $response = $this
            ->graph
            ->createRequest('GET', $endpoint)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception("Unexpected status code produced by 'GET $endpoint': $status");
        }

        $driveItem = $response->getResponseAsObject(Model\DriveItem::class);

        return new DriveItemProxy(
            $this->graph,
            $driveItem,
            $this->serviceDefinition->getResourceDefinition('driveItem')
        );
    }

    /**
     * Gets a special folder by name.
     *
     * See {@see \Krizalys\Onedrive\Constant\SpecialFolderName SpecialFolderName}
     * for the parameter `$specialFolderName`' supported values.
     *
     * @param string $specialFolderName
     *        The special folder name.
     *
     * @return \Krizalys\Onedrive\Proxy\DriveItemProxy
     *         The root drive item.
     *
     * @since 2.0.0
     *
     * @api
     *
     * @link https://docs.microsoft.com/en-us/onedrive/developer/rest-api/api/drive_get_specialfolder?view=odsp-graph-online
     *       Get a special folder by name
     */
    public function getSpecialFolder($specialFolderName)
    {
        $driveLocator         = '/me/drive';
        $specialFolderLocator = "/special/$specialFolderName";
        $endpoint             = "$driveLocator$specialFolderLocator";

        $response = $this
            ->graph
            ->createRequest('GET', $endpoint)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception("Unexpected status code produced by 'GET $endpoint': $status");
        }

        $driveItem = $response->getResponseAsObject(Model\DriveItem::class);

        return new DriveItemProxy(
            $this->graph,
            $driveItem,
            $this->serviceDefinition->getResourceDefinition('driveItem')
        );
    }

    /**
     * Gets items shared with the signed-in user.
     *
     * @return \Krizalys\Onedrive\Proxy\DriveItemProxy[]
     *         The shared drive items.
     *
     * @since 2.0.0
     *
     * @api
     *
     * @link https://docs.microsoft.com/en-us/onedrive/developer/rest-api/api/drive_sharedwithme?view=odsp-graph-online
     *       List items shared with the signed-in user
     */
    public function getShared()
    {
        $driveLocator = '/me/drive';
        $endpoint     = "$driveLocator/sharedWithMe";

        $response = $this
            ->graph
            ->createCollectionRequest('GET', $endpoint)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception("Unexpected status code produced by 'GET $endpoint': $status");
        }

        $driveItems = $response->getResponseAsObject(Model\DriveItem::class);

        if (!is_array($driveItems)) {
            return [];
        }

        return array_map(function (Model\DriveItem $driveItem) {
            return new DriveItemProxy(
                $this->graph,
                $driveItem,
                $this->serviceDefinition->getResourceDefinition('driveItem')
            );
        }, $driveItems);
    }

    /**
     * Gets recent files.
     *
     * @return \Krizalys\Onedrive\Proxy\DriveItemProxy[]
     *         The recent drive items.
     *
     * @since 2.0.0
     *
     * @api
     *
     * @link https://docs.microsoft.com/en-us/onedrive/developer/rest-api/api/drive_recent?view=odsp-graph-online
     *       List recent files
     */
    public function getRecent()
    {
        $driveLocator = '/me/drive';
        $endpoint     = "$driveLocator/recent";

        $response = $this
            ->graph
            ->createCollectionRequest('GET', $endpoint)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception("Unexpected status code produced by 'GET $endpoint': $status");
        }

        $driveItems = $response->getResponseAsObject(Model\DriveItem::class);

        if (!is_array($driveItems)) {
            return [];
        }

        return array_map(function (Model\DriveItem $driveItem) {
            return new DriveItemProxy(
                $this->graph,
                $driveItem,
                $this->serviceDefinition->getResourceDefinition('driveItem')
            );
        }, $driveItems);
    }
}
