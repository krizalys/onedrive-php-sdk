<?php

namespace Krizalys\Onedrive;

use GuzzleHttp\ClientInterface;
use Krizalys\Onedrive\Proxy\DriveItemProxy;
use Krizalys\Onedrive\Proxy\DriveProxy;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

/**
 * @class Client
 *
 * A Client instance allows communication with the OneDrive API and perform
 * operations programmatically.
 *
 * To manage your Live Connect applications, see here:
 * https://apps.dev.microsoft.com/#/appList
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
     *      The legacy date/time format.
     *
     * @deprecated Use ISO-8601 date/times instead.
     */
    const LEGACY_DATETIME_FORMAT = 'Y-m-d\TH:i:sO';

    /**
     * @var string
     *      The client ID.
     */
    private $clientId;

    /**
     * @var Microsoft\Graph\Graph
     *      The Microsoft Graph.
     */
    private $graph;

    /**
     * @var GuzzleHttp\ClientInterface
     *      The Guzzle HTTP client.
     */
    private $httpClient;

    /**
     * @var object
     *      The OAuth state (token, etc...).
     */
    private $_state;

    /**
     * Constructor.
     *
     * @param string $clientId
     *        The client ID.
     * @param Microsoft\Graph\Graph $graph
     *        The graph.
     * @param GuzzleHttp\ClientInterface $httpClient
     *        The HTTP client.
     * @param mixed $logger
     *        Deprecated and will be removed in version 3; omit this parameter,
     *        or pass null or options instead.
     * @param array $options
     *        The options to use while creating this object.
     *        Valid supported keys are:
     *          - 'state' (object) When defined, it should contain a valid
     *            OneDrive client state, as returned by getState(). Default: [].
     *
     * @throws Exception
     *         Thrown if $clientId is null.
     */
    public function __construct(
        $clientId,
        Graph $graph,
        ClientInterface $httpClient,
        $logger = null,
        array $options = []
    ) {
        if (func_num_args() == 4 && is_array($logger)) {
            $options = $logger;
            $logger  = null;
        } elseif ($logger !== null) {
            $message = '$logger is deprecated and will be removed in version 3;'
                . ' omit this parameter, or pass null or options instead';

            @trigger_error($message, E_USER_DEPRECATED);
        }

        if ($clientId === null) {
            throw new \Exception('The client ID must be set');
        }

        $this->clientId   = $clientId;
        $this->graph      = $graph;
        $this->httpClient = $httpClient;

        $this->_state = array_key_exists('state', $options)
            ? $options['state'] : (object) [
                'redirect_uri' => null,
                'token'        => null,
            ];
    }

    /**
     * Gets the current state of this Client instance. Typically saved in the
     * session and passed back to the Client constructor for further requests.
     *
     * @return object
     *         The state of this Client instance.
     */
    public function getState()
    {
        return $this->_state;
    }

    /**
     * Gets the URL of the log in form. After login, the browser is redirected
     * to the redirect URI, and a code is passed as a query string parameter to
     * this URI.
     *
     * The browser is also redirected to the redirect URI if the user is already
     * logged in.
     *
     * @param array $scopes
     *        The OneDrive scopes requested by the application. Supported
     *        values:
     *          - 'offline_access'
     *          - 'files.read'
     *          - 'files.read.all'
     *          - 'files.readwrite'
     *          - 'files.readwrite.all'
     * @param string $redirectUri
     *        The URI to which to redirect to upon successful log in.
     *
     * @return string
     *         The log in URL.
     */
    public function getLogInUrl(array $scopes, $redirectUri)
    {
        $redirectUri                = (string) $redirectUri;
        $this->_state->redirect_uri = $redirectUri;

        $values = [
            'client_id'     => $this->clientId,
            'response_type' => 'code',
            'redirect_uri'  => $redirectUri,
            'scope'         => implode(' ', $scopes),
            'response_mode' => 'query',
        ];

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
     */
    public function getTokenExpire()
    {
        return $this->_state->token->obtained
            + $this->_state->token->data->expires_in - time();
    }

    /**
     * Gets the status of the current access token.
     *
     * @return int
     *         The status of the current access token:
     *           -  0 No access token.
     *           - -1 Access token will expire soon (1 minute or less).
     *           - -2 Access token is expired.
     *           -  1 Access token is valid.
     */
    public function getAccessTokenStatus()
    {
        if (null === $this->_state->token) {
            return 0;
        }

        $remaining = $this->getTokenExpire();

        if (0 >= $remaining) {
            return -2;
        }

        if (60 >= $remaining) {
            return -1;
        }

        return 1;
    }

    /**
     * Obtains a new access token from OAuth. This token is valid for one hour.
     *
     * @param string $clientSecret
     *        The OneDrive client secret.
     * @param string $code
     *        The code returned by OneDrive after successful log in.
     *
     * @throws Exception
     *         Thrown if the redirect URI of this Client instance's state is not
     *         set.
     * @throws Exception
     *         Thrown if the HTTP response body cannot be JSON-decoded.
     */
    public function obtainAccessToken($clientSecret, $code)
    {
        if (null === $this->_state->redirect_uri) {
            throw new \Exception(
                'The state\'s redirect URI must be set to call'
                    . ' obtainAccessToken()'
            );
        }

        $values = [
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->_state->redirect_uri,
            'client_secret' => (string) $clientSecret,
            'code'          => (string) $code,
            'grant_type'    => 'authorization_code',
        ];

        $response = $this->httpClient->post(
            self::TOKEN_URL,
            ['form_params' => $values]
        );

        $body = $response->getBody();
        $data = json_decode($body);

        if ($data === null) {
            throw new \Exception('json_decode() failed');
        }

        $this->_state->redirect_uri = null;

        $this->_state->token = (object) [
            'obtained' => time(),
            'data'     => $data,
        ];

        $this->graph->setAccessToken($this->_state->token->data->access_token);
    }

    /**
     * Renews the access token from OAuth. This token is valid for one hour.
     *
     * @param string $clientSecret
     *        The client secret.
     */
    public function renewAccessToken($clientSecret)
    {
        if (null === $this->_state->token->data->refresh_token) {
            throw new \Exception(
                'The refresh token is not set or no permission for'
                    . ' \'wl.offline_access\' was given to renew the token'
            );
        }

        $values = [
            'client_id'     => $this->clientId,
            'client_secret' => $clientSecret,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->_state->token->data->refresh_token,
        ];

        $response = $this->httpClient->post(
            self::TOKEN_URL,
            ['form_params' => $values]
        );

        $body = $response->getBody();
        $data = json_decode($body);

        if ($data === null) {
            throw new \Exception('json_decode() failed');
        }

        $this->_state->token = (object) [
            'obtained' => time(),
            'data'     => $data,
        ];
    }

    /**
     * @return array
     *         The drives.
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
            return new DriveProxy($this->graph, $drive);
        }, $drives);
    }

    /**
     * @return DriveProxy
     *         The drive.
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
            throw new \Exception();
        }

        $drive = $response->getResponseAsObject(Model\Drive::class);

        return new DriveProxy($this->graph, $drive);
    }

    /**
     * @param string $driveId
     *        The drive ID.
     *
     * @return DriveProxy
     *         The drive.
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
            throw new \Exception();
        }

        $drive = $response->getResponseAsObject(Model\Drive::class);

        return new DriveProxy($this->graph, $drive);
    }

    /**
     * @param string $idOrUserPrincipalName
     *        The ID or user principal name.
     *
     * @return DriveProxy
     *         The drive.
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
            throw new \Exception();
        }

        $drive = $response->getResponseAsObject(Model\Drive::class);

        return new DriveProxy($this->graph, $drive);
    }

    /**
     * @param string $groupId
     *        The group ID.
     *
     * @return DriveProxy
     *         The drive.
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
            throw new \Exception();
        }

        $drive = $response->getResponseAsObject(Model\Drive::class);

        return new DriveProxy($this->graph, $drive);
    }

    /**
     * @param string $siteId
     *        The site ID.
     *
     * @return DriveProxy
     *         The drive.
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
            throw new \Exception();
        }

        $drive = $response->getResponseAsObject(Model\Drive::class);

        return new DriveProxy($this->graph, $drive);
    }

    /**
     * @param string $driveId
     *        The drive ID.
     * @param string $itemId
     *        The drive item ID.
     *
     * @return DriveItemProxy
     *         The drive item.
     */
    public function getDriveItemById($driveId, $itemId)
    {
        $driveLocator = "/drives/$driveId";
        $itemLocator  = "/items/$itemId";
        $endpoint     = "$driveLocator$itemLocator";

        $response = $this
            ->graph
            ->createRequest('GET', $endpoint)
            ->execute();

        $status = $response->getStatus();

        if ($status != 200) {
            throw new \Exception();
        }

        $driveItem = $response->getResponseAsObject(Model\DriveItem::class);

        return new DriveItemProxy($this->graph, $driveItem);
    }

    /**
     * @return DriveItemProxy
     *         The root drive item.
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

        return new DriveItemProxy($this->graph, $driveItem);
    }

    /**
     * @param string $specialFolderName
     *        The special folder name. Supported values:
     *          - 'documents'
     *          - 'photos'
     *          - 'cameraroll'
     *          - 'approot'
     *          - 'music'
     *
     * @return DriveItemProxy
     *         The root drive item.
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

        return new DriveItemProxy($this->graph, $driveItem);
    }

    /**
     * @return array
     *         The shared drive items.
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
            return new DriveItemProxy($this->graph, $driveItem);
        }, $driveItems);
    }

    /**
     * @return array
     *         The recent drive items.
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
            return new DriveItemProxy($this->graph, $driveItem);
        }, $driveItems);
    }

    // Legacy support //////////////////////////////////////////////////////////

    /**
     * Creates a folder in the current OneDrive account.
     *
     * @param string $name
     *        The name of the OneDrive folder to be created.
     * @param null|string $parentId
     *        The ID of the OneDrive folder into which to create the OneDrive
     *        folder, or null to create it in the OneDrive root folder. Default:
     *        null.
     * @param null|string $description
     *        The description of the OneDrive folder to be created, or null to
     *        create it without a description. Default: null.
     *
     * @return Folder
     *         The folder created, as a Folder instance referencing to the
     *         OneDrive folder created.
     *
     * @deprecated Use Krizalys\Onedrive\Proxy\DriveItemProxy::createFolder()
     *             instead.
     */
    public function createFolder($name, $parentId = null, $description = null)
    {
        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Proxy\DriveItemProxy::createFolder()'
                . ' instead',
            __METHOD__
        );

        @trigger_error($message, E_USER_DEPRECATED);
        $drive = $this->getMyDrive();

        $item = $parentId !== null ?
            $this->getDriveItemById($drive->id, $parentId)
            : $drive->getRoot();

        $options = [];

        if ($description !== null) {
            $options += [
                'description' => (string) $description,
            ];
        }

        $item    = $item->createFolder($name, $options);
        $options = $this->buildOptions($item, ['parent_id' => $parentId]);

        return new Folder($this, $item->id, $options);
    }

    /**
     * Creates a file in the current OneDrive account.
     *
     * @param string $name
     *        The name of the OneDrive file to be created.
     * @param null|string $parentId
     *        The ID of the OneDrive folder into which to create the OneDrive
     *        file, or null to create it in the OneDrive root folder. Default:
     *        null.
     * @param string|resource|\GuzzleHttp\Psr7\Stream $content
     *        The content of the OneDrive file to be created, as a string or as
     *        a resource to an already opened file. Default: ''.
     * @param array $options
     *        The options.
     *
     * @return File
     *         The file created, as File instance referencing to the OneDrive
     *         file created.
     *
     * @throws Exception
     *         Thrown on I/O errors.
     *
     * @deprecated Use Krizalys\Onedrive\Proxy\DriveItemProxy::upload() instead.
     *
     * @todo Support name conflict behavior.
     * @todo Support content type in options.
     */
    public function createFile(
        $name,
        $parentId = null,
        $content = '',
        array $options = []
    ) {
        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Proxy\DriveItemProxy::upload()'
                . ' instead',
            __METHOD__
        );

        @trigger_error($message, E_USER_DEPRECATED);
        $drive = $this->getMyDrive();

        $item = $parentId !== null ?
            $this->getDriveItemById($drive->id, $parentId)
            : $drive->getRoot();

        $item    = $item->upload($name, $content);
        $options = $this->buildOptions($item, ['parent_id' => $parentId]);

        return new File($this, $item->id, $options);
    }

    /**
     * Fetches a drive item from the current OneDrive account.
     *
     * @param null|string $driveItemId
     *        The unique ID of the OneDrive drive item to fetch, or null to
     *        fetch the OneDrive root folder. Default: null.
     *
     * @return object
     *         The drive item fetched, as a DriveItem instance referencing to
     *         the OneDrive drive item fetched.
     *
     * @deprecated Use Krizalys\Onedrive\Client::getDriveItemById() instead.
     */
    public function fetchDriveItem($driveItemId = null)
    {
        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Client::getDriveItemById() instead.',
            __METHOD__
        );

        @trigger_error($message, E_USER_DEPRECATED);
        $drive = $this->getMyDrive();

        $item = $driveItemId !== null ?
            $this->getDriveItemById($drive->id, $driveItemId)
            : $drive->getRoot();

        $options = $this->buildOptions($item, ['parent_id' => $driveItemId]);

        return $this->isFolder($item) ?
            new Folder($this, $item->id, $options)
            : new File($this, $item->id, $options);
    }

    /**
     * Fetches the root folder from the current OneDrive account.
     *
     * @return Folder
     *         The root folder, as a Folder instance referencing to the OneDrive
     *         root folder.
     *
     * @deprecated Use Krizalys\Onedrive\Client::getRoot() instead.
     */
    public function fetchRoot()
    {
        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Client::getRoot() instead.',
            __METHOD__
        );

        @trigger_error($message, E_USER_DEPRECATED);
        $item    = $this->getRoot();
        $options = $this->buildOptions($item);

        return new Folder($this, $item->id, $options);
    }

    /**
     * Fetches the "Camera Roll" folder from the current OneDrive account.
     *
     * @return Folder
     *         The "Camera Roll" folder, as a Folder instance referencing to the
     *         OneDrive "Camera Roll" folder.
     *
     * @deprecated Use Krizalys\Onedrive\Client::getSpecialFolder() instead.
     */
    public function fetchCameraRoll()
    {
        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Client::getSpecialFolder() instead.',
            __METHOD__
        );

        @trigger_error($message, E_USER_DEPRECATED);
        $item    = $this->getSpecialFolder('cameraroll');
        $options = $this->buildOptions($item);

        return new Folder($this, $item->id, $options);
    }

    /**
     * Fetches the "Documents" folder from the current OneDrive account.
     *
     * @return Folder
     *         The "Documents" folder, as a Folder instance referencing to the
     *         OneDrive "Documents" folder.
     *
     * @deprecated Use Krizalys\Onedrive\Client::getSpecialFolder() instead.
     */
    public function fetchDocs()
    {
        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Client::getSpecialFolder() instead.',
            __METHOD__
        );

        @trigger_error($message, E_USER_DEPRECATED);
        $item    = $this->getSpecialFolder('documents');
        $options = $this->buildOptions($item);

        return new Folder($this, $item->id, $options);
    }

    /**
     * Fetches the "Pictures" folder from the current OneDrive account.
     *
     * @return Folder
     *         The "Pictures" folder, as a Folder instance referencing to the
     *         OneDrive "Pictures" folder.
     *
     * @deprecated Use Krizalys\Onedrive\Client::getSpecialFolder() instead.
     */
    public function fetchPics()
    {
        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Client::getSpecialFolder() instead.',
            __METHOD__
        );

        @trigger_error($message, E_USER_DEPRECATED);
        $item    = $this->getSpecialFolder('photos');
        $options = $this->buildOptions($item);

        return new Folder($this, $item->id, $options);
    }

    /**
     * Fetches the properties of a drive item in the current OneDrive account.
     *
     * @param null|string $driveItemId
     *        The drive item ID, or null to fetch the OneDrive root folder.
     *        Default: null.
     *
     * @return object
     *         The properties of the drive item fetched.
     *
     * @deprecated Use Krizalys\Onedrive\Client::getDriveItemById() instead.
     */
    public function fetchProperties($driveItemId = null)
    {
        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Client::getDriveItemById() instead',
            __METHOD__
        );

        @trigger_error($message, E_USER_DEPRECATED);
        $drive = $this->getMyDrive();

        $item = $driveItemId !== null ?
            $this->getDriveItemById($drive->id, $driveItemId)
            : $drive->getRoot();

        $options = $this->buildOptions(
            $item,
            [
                'id'        => $item->id,
                'parent_id' => $driveItemId,
            ]
        );

        return (object) $options;
    }

    /**
     * Fetches the drive items in a folder in the current OneDrive account.
     *
     * @param null|string $driveItemId
     *        The drive item ID, or null to fetch the OneDrive root folder.
     *        Default: null.
     *
     * @return array
     *         The drive items in the folder fetched, as DriveItem instances
     *         referencing OneDrive drive items.
     *
     * @deprecated Use Krizalys\Onedrive\Proxy\DriveItemProxy::children
     *             instead.
     */
    public function fetchDriveItems($driveItemId = null)
    {
        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Proxy\DriveItemProxy::children'
                . ' instead.',
            __METHOD__
        );

        @trigger_error($message, E_USER_DEPRECATED);
        $drive = $this->getMyDrive();

        $item = $driveItemId !== null ?
            $this->getDriveItemById($drive->id, $driveItemId)
            : $drive->getRoot();

        return array_map(function (DriveItemProxy $item) use ($driveItemId) {
            $options = $this->buildOptions($item, ['parent_id' => $driveItemId]);

            return $this->isFolder($item) ?
                new Folder($this, $item->id, $options)
                : new File($this, $item->id, $options);
        }, $item->children);
    }

    /**
     * Updates the properties of a drive item in the current OneDrive account.
     *
     * @param string $driveItemId
     *        The unique ID of the drive item to update.
     * @param array|object $properties
     *        The properties to update. Default: [].
     * @param bool $temp
     *        Option to allow save to a temporary file in case of large files.
     *
     * @throws Exception
     *         Thrown on I/O errors.
     *
     * @deprecated Use Krizalys\Onedrive\Proxy\DriveItemProxy::rename() instead.
     */
    public function updateDriveItem($driveItemId, $properties = [], $temp = false)
    {
        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Proxy\DriveItemProxy::rename()'
                . ' instead',
            __METHOD__
        );

        @trigger_error($message, E_USER_DEPRECATED);
        $drive = $this->getMyDrive();

        $item = $driveItemId !== null ?
            $this->getDriveItemById($drive->id, $driveItemId)
            : $drive->getRoot();

        $options = (array) $properties;

        if (array_key_exists('name', $options)) {
            $name = $options['name'];
            unset($options['name']);
        } else {
            $name = $item->name;
        }

        $item    = $item->rename($name, $options);
        $options = $this->buildOptions($item, ['parent_id' => $driveItemId]);

        return new Folder($this, $item->id, $options);
    }

    /**
     * Moves a drive item into another folder.
     *
     * @param string $driveItemId
     *        The unique ID of the drive item to move.
     * @param null|string $destinationId
     *        The unique ID of the folder into which to move the drive item, or
     *        null to move it to the OneDrive root folder. Default: null.
     *
     * @deprecated Use Krizalys\Onedrive\Proxy\DriveItemProxy::move() instead.
     */
    public function moveDriveItem($driveItemId, $destinationId = null)
    {
        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Proxy\DriveItemProxy::move()'
                . ' instead.',
            __METHOD__
        );

        @trigger_error($message, E_USER_DEPRECATED);
        $drive = $this->getMyDrive();
        $item  = $this->getDriveItemById($drive->id, $driveItemId);

        $destination = $destinationId !== null ?
            $this->getDriveItemById($drive->id, $destinationId)
            : $drive->getRoot();

        $item->move($destination);
    }

    /**
     * Copies a file into another folder. OneDrive does not support copying
     * folders.
     *
     * @param string $driveItemId
     *        The unique ID of the file to copy.
     * @param null|string $destinationId
     *        The unique ID of the folder into which to copy the file, or null
     *        to copy it to the OneDrive root folder. Default: null.
     *
     * @deprecated Use Krizalys\Onedrive\Proxy\DriveItemProxy::copy() instead.
     */
    public function copyFile($driveItemId, $destinationId = null)
    {
        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Proxy\DriveItemProxy::copy()'
                . ' instead.',
            __METHOD__
        );

        @trigger_error($message, E_USER_DEPRECATED);
        $drive = $this->getMyDrive();
        $item  = $this->getDriveItemById($drive->id, $driveItemId);

        $destination = $destinationId !== null ?
            $this->getDriveItemById($drive->id, $destinationId)
            : $drive->getRoot();

        $item->copy($destination);
    }

    /**
     * Deletes a drive item in the current OneDrive account.
     *
     * @param string $driveItemId
     *        The unique ID of the drive item to delete.
     *
     * @deprecated Use Krizalys\Onedrive\Proxy\DriveItemProxy::delete() instead.
     */
    public function deleteDriveItem($driveItemId)
    {
        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Proxy\DriveItemProxy::delete()'
                . ' instead',
            __METHOD__
        );

        @trigger_error($message, E_USER_DEPRECATED);
        $drive = $this->getMyDrive();
        $item  = $this->getDriveItemById($drive->id, $driveItemId);
        $item->delete();
    }

    /**
     * Fetches the quota of the current OneDrive account.
     *
     * @return object
     *         An object with the following properties:
     *           - 'quota' (int) The total space, in bytes.
     *           - 'available' (int) The available space, in bytes.
     *
     * @deprecated Use Krizalys\Onedrive\Proxy\DriveProxy::quota instead.
     */
    public function fetchQuota()
    {
        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Proxy\DriveProxy::quota instead',
            __METHOD__
        );

        @trigger_error($message, E_USER_DEPRECATED);
        $drive = $this->getMyDrive();
        $quota = $drive->quota;

        return (object) [
            'quota'     => $quota->total,
            'available' => $quota->remaining,
        ];
    }

    /**
     * Fetches the recent documents uploaded to the current OneDrive account.
     *
     * @return object
     *         An object with the following properties:
     *           - 'data' (array) The list of the recent documents uploaded.
     *
     * @deprecated Use Krizalys\Onedrive\Client::getRecent() instead.
     */
    public function fetchRecentDocs()
    {
        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Client::getRecent() instead',
            __METHOD__
        );

        @trigger_error($message, E_USER_DEPRECATED);
        $items = $this->getRecent();

        return (object) [
            'data' => array_map(function (DriveItemProxy $item) {
                return (object) $this->buildOptions($item);
            }, $items),
        ];
    }

    /**
     * Fetches the drive items shared with the current OneDrive account.
     *
     * @return object
     *         An object with the following properties:
     *           - 'data' (array) The list of the shared drive items.
     *
     * @deprecated Use Krizalys\Onedrive\Client::getShared() instead.
     */
    public function fetchShared()
    {
        $message = sprintf(
            '%s() is deprecated and will be removed in version 3;'
                . ' use Krizalys\Onedrive\Client::getShared() instead',
            __METHOD__
        );

        @trigger_error($message, E_USER_DEPRECATED);
        $items = $this->getShared();

        return (object) [
            'data' => array_map(function (DriveItemProxy $item) {
                return (object) $this->buildOptions($item);
            }, $items),
        ];
    }

    /**
     * Checks whether a given drive item is a folder.
     *
     * @param DriveItemProxy $item
     *        The drive item.
     *
     * @return bool
     *         Whether the drive item is a folder.
     */
    public function isFolder(DriveItemProxy $item)
    {
        return $item->folder !== null || $item->specialFolder !== null;
    }

    /**
     * @param DriveItemProxy $item
     *        The drive item.
     * @param array $options
     *        The options.
     *
     * @return array
     *         The options.
     */
    public function buildOptions(DriveItemProxy $item, array $options = [])
    {
        $defaultOptions = [
            'from' => (object) [
                'name' => null,
                'id'   => null,
            ],
        ];

        if ($item->id !== null) {
            $defaultOptions['id'] = $item->id;
        }

        if ($item->parentReference->id !== null) {
            $defaultOptions['parent_id'] = $item->parentReference->id;
        }

        if ($item->name !== null) {
            $defaultOptions['name'] = $item->name;
        }

        if ($item->description !== null) {
            $defaultOptions['description'] = $item->description;
        }

        if ($item->size !== null) {
            $defaultOptions['size'] = $item->size;
        }

        if ($item->createdDateTime !== null) {
            $defaultOptions['created_time'] = $item->createdDateTime->format(self::LEGACY_DATETIME_FORMAT);
        }

        if ($item->lastModifiedDateTime !== null) {
            $defaultOptions['updated_time'] = $item->lastModifiedDateTime->format(self::LEGACY_DATETIME_FORMAT);
        }

        return $defaultOptions + $options;
    }
}
