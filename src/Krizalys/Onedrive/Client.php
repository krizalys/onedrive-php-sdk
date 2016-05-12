<?php

namespace Krizalys\Onedrive;

// TODO: Support refresh tokens, see:
// http://msdn.microsoft.com/en-us/library/live/hh243647.aspx
// TODO: Pass parameters in POST request body when obtaining the access token.
/**
 * @class Client
 *
 * A Client instance allows communication with the OneDrive API and perform
 * operations programmatically.
 *
 * For an overview of the OneDrive protocol flow, see here:
 * http://msdn.microsoft.com/en-us/library/live/hh243647.aspx
 *
 * To manage your Live Connect applications, see here:
 * https://account.live.com/developers/applications/index
 * Or here:
 * https://manage.dev.live.com/ (not working?)
 *
 * For an example implementation, see here:
 * https://github.com/drumaddict/skydrive-api-yii/blob/master/SkyDriveAPI.php
 */
class Client
{
    /**
     * @var string The base URL for API requests.
     */
    const API_URL = 'https://apis.live.net/v5.0/';

    /**
     * @var string The base URL for authorization requests.
     */
    const AUTH_URL = 'https://login.live.com/oauth20_authorize.srf';

    /**
     * @var string The base URL for token requests.
     */
    const TOKEN_URL = 'https://login.live.com/oauth20_token.srf';

    /**
     * @var string Client information.
     */
    private $_clientId;

    /**
     * @var object OAuth state (token, etc...).
     */
    private $_state;

    /**
     * @var int The last HTTP status received.
     */
    private $_httpStatus;

    /**
     * @var string The last Content-Type received.
     */
    private $_contentType;

    /**
     * @var bool Verify SSL hosts and peers.
     */
    private $_sslVerify;

    /**
     * @var null|string Over-ride SSL CA path for verification (only relevant
     *                  when verifying).
     */
    private $_sslCaPath;

    /**
     * Creates a base cURL object which is compatible with the OneDrive API.
     *
     * @param string $path    The path of the API call (eg. me/skydrive).
     * @param array  $options Extra cURL options to apply.
     *
     * @return resource A compatible cURL object.
     */
    private function _createCurl($path, $options = array())
    {
        $curl = curl_init();

        $defaultOptions = array(
            // General options.
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER    => true,

            // SSL options.
            // The value 2 checks the existence of a common name and also
            // verifies that it matches the hostname provided.
            CURLOPT_SSL_VERIFYHOST => ($this->_sslVerify ? 2 : false),

            CURLOPT_SSL_VERIFYPEER => $this->_sslVerify
        );

        if ($this->_sslVerify && $this->_sslCaPath) {
            $default_options[CURLOPT_CAINFO] = $this->_sslCaPath;
        }

        // See http://php.net/manual/en/function.array-merge.php for a
        // description of the + operator (and why array_merge() would be wrong).
        $finalOptions = $options + $defaultOptions;

        curl_setopt_array($curl, $finalOptions);
        return $curl;
    }

    /**
     * Processes a result returned by the OneDrive API call using a cURL object.
     *
     * @param resource $curl The cURL object used to perform the call.
     *
     * @return object|string The content returned, as an object instance if
     *                       served a JSON, or as a string if served as anything
     *                       else.
     *
     * @throws \Exception Thrown if curl_exec() fails.
     */
    private function _processResult($curl)
    {
        $result = curl_exec($curl);

        if (false === $result) {
            throw new \Exception('curl_exec() failed: ' . curl_error($curl));
        }

        $info = curl_getinfo($curl);

        $this->_httpStatus = array_key_exists('http_code', $info) ?
            (int) $info['http_code'] : null;

        $this->_contentType = array_key_exists('content_type', $info) ?
            (string) $info['content_type'] : null;

        // Parse nothing but JSON.
        if (1 !== preg_match('|^application/json|', $this->_contentType)) {
            return $result;
        }

        // Empty JSON string is returned as an empty object.
        if ('' == $result) {
            return (object) array();
        }

        $decoded = json_decode($result);
        $vars    = get_object_vars($decoded);

        if (array_key_exists('error', $vars)) {
            throw new \Exception($decoded->error->message, (int) $decoded->error->code);
        }

        return $decoded;
    }

    /**
     * Constructor.
     *
     * @param array $options The options to use while creating this object.
     *                       Valid supported keys are:
     *                         'state'      (object)      When defined, it
     *                                                    should contain a valid
     *                                                    OneDrive client state,
     *                                                    as returned by
     *                                                    getState(). Default:
     *                                                    array().
     *                         'ssl_verify' (bool)        Whether to verify SSL
     *                                                    hosts and peers.
     *                                                    Default: false.
     *                         'ssl_capath' (bool|string) CA path to use for
     *                                                    verifying SSL
     *                                                    certificate chain.
     *                                                    Default: false.
     */
    public function __construct(array $options = array())
    {
        $this->_clientId = array_key_exists('client_id', $options)
            ? (string) $options['client_id'] : null;

        $this->_state = array_key_exists('state', $options)
            ? $options['state'] : (object) array(
                'redirect_uri' => null,
                'token'        => null
            );

        $this->_sslVerify = array_key_exists('ssl_verify', $options)
            ? $options['ssl_verify'] : false;

        $this->_sslCaPath = array_key_exists('ssl_capath', $options)
            ? $options['ssl_capath'] : false;
    }

    /**
     * Gets the current state of this Client instance. Typically saved in the
     * session and passed back to the Client constructor for further requests.
     *
     * @return object The state of this Client instance.
     */
    public function getState()
    {
        return $this->_state;
    }

    // TODO: support $options.
    /**
     * Gets the URL of the log in form. After login, the browser is redirected to
     * the redirect URL, and a code is passed as a GET parameter to this URL.
     *
     * The browser is also redirected to this URL if the user is already logged
     * in.
     *
     * @param array $scopes       The OneDrive scopes requested by the
     *                            application. Supported values:
     *                              'wl.signin'
     *                              'wl.basic'
     *                              'wl.contacts_skydrive'
     *                              'wl.skydrive_update'
     * @param string $redirectUri The URI to which to redirect to upon
     *                            successful log in.
     * @param array  $options     Reserved for future use. Default: array().
     *
     * @return string The login URL.
     *
     * @throws \Exception Thrown if this Client instance's clientId is not set.
     */
    public function getLogInUrl(array $scopes, $redirectUri, array $options = array())
    {
        if (null === $this->_clientId) {
            throw new \Exception('The client ID must be set to call getLoginUrl()');
        }

        $imploded    = implode(',', $scopes);
        $redirectUri = (string) $redirectUri;
        $this->_state->redirect_uri = $redirectUri;

        // When using this URL, the browser will eventually be redirected to the
        // callback URL with a code passed in the URL query string (the name of the
        // variable is "code"). This is suitable for PHP.
        $url = self::AUTH_URL
            . '?client_id=' . urlencode($this->_clientId)
            . '&scope=' . urlencode($imploded)
            . '&response_type=code'
            . '&redirect_uri=' . urlencode($redirectUri)
            . '&display=popup'
            . '&locale=en';

        return $url;
    }

    /**
     * Gets the access token expiration delay.
     *
     * @return int The token expiration delay, in seconds.
     */
    public function getTokenExpire()
    {
        return $this->_state->token->obtained
            + $this->_state->token->data->expires_in - time();
    }

    /**
     * Gets the status of the current access token.
     *
     * @return int The status of the current access token:
     *                0 No access token.
     *               -1 Access token will expire soon (1 minute or less).
     *               -2 Access token is expired.
     *                1 Access token is valid.
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
     * @param string $clientSecret The OneDrive client secret.
     * @param string $code         The code returned by OneDrive after
     *                             successful log in.
     * @param string $redirectUri  Must be the same as the redirect URI passed
     *                             to getLoginUrl().
     *
     * @throws \Exception Thrown if this Client instance's clientId is not set.
     * @throws \Exception Thrown if the redirect URI of this Client instance's
     *                    state is not set.
     */
    public function obtainAccessToken($clientSecret, $code)
    {
        if (null === $this->_clientId) {
            throw new \Exception('The client ID must be set to call obtainAccessToken()');
        }

        if (null === $this->_state->redirect_uri) {
            throw new \Exception('The state\'s redirect URI must be set to call obtainAccessToken()');
        }

        $url = self::TOKEN_URL;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            // General options.
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_POST           => 1, // i am sending post data
            CURLOPT_POSTFIELDS     => 'client_id=' . urlencode($this->_clientId)
                . '&redirect_uri=' . urlencode($this->_state->redirect_uri)
                . '&client_secret=' . urlencode($clientSecret)
                . '&grant_type=authorization_code'
                . '&code=' . urlencode($code),

            // SSL options.
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL            => $url
        ));

        $result = curl_exec($curl);

        if (false === $result) {
            if (curl_errno($curl)) {
                throw new \Exception('curl_setopt_array() failed: ' . curl_error($curl));
            } else {
                throw new \Exception('curl_setopt_array(): empty response');
            }
        }

        $decoded = json_decode($result);

        if (null === $decoded) {
            throw new \Exception('json_decode() failed');
        }

        $this->_state->redirect_uri = null;
        $this->_state->token = (object) array(
            'obtained' => time(),
            'data'     => $decoded
        );
    }

    /**
     * Renews the access token from OAuth. This token is valid for one hour.
     *
     * @param string $clientSecret The client secret.
     * @param string $redirectUri  The redirect URI.
     */
    /*public function renewAccessToken($clientSecret, $redirectUri)
    {
        $url = self::TOKEN_URL
            . '?client_id=' . $this->_clientId
            . '&redirect_uri=' . (string) $redirectUri
            . '&client_secret=' . (string) $clientSecret
            . '&grant_type=' . 'refresh_token'
            . '&code=' . (string) $code;
    }*/

    /**
     * Performs a call to the OneDrive API using the GET method.
     *
     * @param string $path    The path of the API call (eg. me/skydrive).
     * @param array  $options Further curl options to set.
     *
     * @return object|string The response body, if any.
     */
    public function apiGet($path, $options = array())
    {
        $url = self::API_URL . $path
            . '?access_token=' . urlencode($this->_state->token->data->access_token);

        $curl = self::_createCurl($path, $options);

        curl_setopt($curl, CURLOPT_URL, $url);
        return $this->_processResult($curl);
    }

    /**
     * Performs a call to the OneDrive API using the POST method.
     *
     * @param string       $path The path of the API call (eg. me/skydrive).
     * @param array|object $data The data to pass in the body of the request.
     *
     * @return object|string The response body, if any.
     */
    public function apiPost($path, $data)
    {
        $url  = self::API_URL . $path;
        $data = (object) $data;
        $curl = self::_createCurl($path);

        curl_setopt_array($curl, array(
            CURLOPT_URL        => $url,
            CURLOPT_POST       => true,

            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json', // The data is sent as JSON as per OneDrive documentation
                'Authorization: Bearer ' . $this->_state->token->data->access_token
            ),

            CURLOPT_POSTFIELDS => json_encode($data)
        ));

        return $this->_processResult($curl);
    }

    /**
     * Performs a call to the OneDrive API using the PUT method.
     *
     * @param string   $path        The path of the API call (eg. me/skydrive).
     * @param resource $stream      The data stream to upload.
     * @param string   $contentType The MIME type of the data stream, or null if
     *                              unknown. Default: null.
     *
     * @return object|string The response body, if any.
     */
    public function apiPut($path, $stream, $contentType = null)
    {
        $url   = self::API_URL . $path;
        $curl  = self::_createCurl($path);
        $stats = fstat($stream);

        $headers = array(
            'Authorization: Bearer ' . $this->_state->token->data->access_token
        );

        if (null !== $contentType) {
            $headers[] = 'Content-Type: ' . $contentType;
        }

        $options = array(
            CURLOPT_URL        => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_PUT        => true,
            CURLOPT_INFILE     => $stream,
            CURLOPT_INFILESIZE => $stats[7] // Size
        );

        curl_setopt_array($curl, $options);
        return $this->_processResult($curl);
    }

    /**
     * Performs a call to the OneDrive API using the DELETE method.
     *
     * @param string $path The path of the API call (eg. me/skydrive).
     *
     * @return object|string The response body, if any.
     */
    public function apiDelete($path)
    {
        $url = self::API_URL . $path
            . '?access_token=' . urlencode($this->_state->token->data->access_token);

        $curl = self::_createCurl($path);

        curl_setopt_array($curl, array(
            CURLOPT_URL           => $url,
            CURLOPT_CUSTOMREQUEST => 'DELETE'
        ));

        return $this->_processResult($curl);
    }

    /**
     * Performs a call to the OneDrive API using the MOVE method.
     *
     * @param string       $path The path of the API call (eg. me/skydrive).
     * @param array|object $data The data to pass in the body of the request.
     *
     * @return object|string The response body, if any.
     */
    public function apiMove($path, $data)
    {
        $url  = self::API_URL . $path;
        $data = (object) $data;
        $curl = self::_createCurl($path);

        curl_setopt_array($curl, array(
            CURLOPT_URL           => $url,
            CURLOPT_CUSTOMREQUEST => 'MOVE',

            CURLOPT_HTTPHEADER    => array(
                'Content-Type: application/json', // The data is sent as JSON as per OneDrive documentation
                'Authorization: Bearer ' . $this->_state->token->data->access_token
            ),

            CURLOPT_POSTFIELDS    => json_encode($data)
        ));

        return $this->_processResult($curl);
    }

    /**
     * Performs a call to the OneDrive API using the COPY method.
     *
     * @param string       $path The path of the API call (eg. me/skydrive).
     * @param array|object $data The data to pass in the body of the request.
     *
     * @return object|string The response body, if any.
     */
    public function apiCopy($path, $data)
    {
        $url  = self::API_URL . $path;
        $data = (object) $data;
        $curl = self::_createCurl($path);

        curl_setopt_array($curl, array(
            CURLOPT_URL           => $url,
            CURLOPT_CUSTOMREQUEST => 'COPY',

            CURLOPT_HTTPHEADER    => array(
                'Content-Type: application/json', // The data is sent as JSON as per OneDrive documentation
                'Authorization: Bearer ' . $this->_state->token->data->access_token
            ),

            CURLOPT_POSTFIELDS    => json_encode($data)
        ));

        return $this->_processResult($curl);
    }

    /**
     * Creates a folder in the current OneDrive account.
     *
     * @param string      $name        The name of the OneDrive folder to be
     *                                 created.
     * @param null|string $parentId    The ID of the OneDrive folder into which
     *                                 to create the OneDrive folder, or null to
     *                                 create it in the OneDrive root folder.
     *                                 Default: null.
     * @param null|string $description The description of the OneDrive folder to
     *                                 be created, or null to create it without
     *                                 a description. Default: null.
     *
     * @return Folder The folder created, as a Folder instance referencing to
     *                the OneDrive folder created.
     */
    public function createFolder($name, $parentId = null, $description = null)
    {
        if (null === $parentId) {
            $parentId = 'me/skydrive';
        }

        $properties = array(
            'name' => (string) $name
        );

        if (null !== $description) {
            $properties['description'] = (string) $description;
        }

        $folder = $this->apiPost($parentId, (object) $properties);
        return new Folder($this, $folder->id, $folder);
    }

    /**
     * Creates a file in the current OneDrive account.
     *
     * @param string      $name        The name of the OneDrive file to be
     *                                 created.
     * @param null|string $parentId    The ID of the OneDrive folder into which
     *                                 to create the OneDrive file, or null to
     *                                 create it in the OneDrive root folder.
     *                                 Default: null.
     * @param string|resource $content The content of the OneDrive file to be
     *                                 created, as a string or as a resource to
     *                                 an already opened file. In the latter
     *                                 case, the responsibility to close the
     *                                 handle is left to the calling function.
     *                                 Default: ''.
     *
     * @return File The file created, as File instance referencing to the
     *              OneDrive file created.
     *
     * @throws \Exception Thrown on I/O errors.
     */
    public function createFile($name, $parentId = null, $content = '')
    {
        if (null === $parentId) {
            $parentId = 'me/skydrive';
        }

        if (is_resource($content)) {
            $stream = $content;
        } else {
            $stream = fopen('php://memory', 'w+b');

            if (false === $stream) {
                throw new \Exception('fopen() failed');
            }

            if (false === fwrite($stream, $content)) {
                fclose($stream);
                throw new \Exception('fwrite() failed');
            }

            if (!rewind($stream)) {
                fclose($stream);
                throw new \Exception('rewind() failed');
            }
        }

        // TODO: some versions of cURL cannot PUT memory streams? See here for a
        // workaround: https://bugs.php.net/bug.php?id=43468
        $file = $this->apiPut($parentId . '/files/' . urlencode($name), $stream);

        // Close the handle only if we opened it within this function.
        if (!is_resource($content)) {
            fclose($stream);
        }

        return new File($this, $file->id, $file);
    }

    /**
     * Fetches an object from the current OneDrive account.
     *
     * @param null|string The unique ID of the OneDrive object to fetch, or null
     *                    to fetch the OneDrive root folder. Default: null.
     *
     * @return object The object fetched, as an Object instance referencing to
     *                the OneDrive object fetched.
     */
    public function fetchObject($objectId = null)
    {
        $objectId = null !== $objectId ? $objectId : 'me/skydrive';
        $result   = $this->apiGet($objectId);

        if (in_array($result->type, array('folder', 'album'))) {
            return new Folder($this, $objectId, $result);
        }

        return new File($this, $objectId, $result);
    }

    /**
     * Fetches the root folder from the current OneDrive account.
     *
     * @return Folder The root folder, as a Folder instance referencing to the
     *         OneDrive root folder.
     */
    public function fetchRoot()
    {
        return $this->fetchObject();
    }

    /**
     * Fetches the "Camera Roll" folder from the current OneDrive account.
     *
     * @return Folder The "Camera Roll" folder, as a Folder instance referencing
     *                to the OneDrive "Camera Roll" folder.
     */
    public function fetchCameraRoll()
    {
        return $this->fetchObject('me/skydrive/camera_roll');
    }

    /**
     * Fetches the "Documents" folder from the current OneDrive account.
     *
     * @return Folder The "Documents" folder, as a Folder instance referencing
     *                to the OneDrive "Documents" folder.
     */
    public function fetchDocs()
    {
        return $this->fetchObject('me/skydrive/my_documents');
    }

    /**
     * Fetches the "Pictures" folder from the current OneDrive account.
     *
     * @return Folder The "Pictures" folder, as a Folder instance referencing to
     *                the OneDrive "Pictures" folder.
     */
    public function fetchPics()
    {
        return $this->fetchObject('me/skydrive/my_photos');
    }

    /**
     * Fetches the "Public" folder from the current OneDrive account.
     *
     * @return Folder The "Public" folder, as a Folder instance referencing to
     *                the OneDrive "Public" folder.
     */
    public function fetchPublicDocs()
    {
        return $this->fetchObject('me/skydrive/public_documents');
    }

    /**
     * Fetches the properties of an object in the current OneDrive account.
     *
     * @return object The properties of the object fetched.
     */
    public function fetchProperties($objectId)
    {
        if (null === $objectId) {
            $objectId = 'me/skydrive';
        }

        return $this->apiGet($objectId);
    }

    /**
     * Fetches the objects in a folder in the current OneDrive account.
     *
     * @return array The objects in the folder fetched, as Object instances
     *               referencing OneDrive objects.
     */
    public function fetchObjects($objectId)
    {
        if (null === $objectId) {
            $objectId = 'me/skydrive';
        }

        $result   = $this->apiGet($objectId . '/files');
        $objects  = array();

        foreach ($result->data as $data) {
            $object = in_array($data->type, array('folder', 'album')) ?
                new Folder($this, $data->id, $data)
                : new File($this, $data->id, $data);

            $objects[] = $object;
        }

        return $objects;
    }

    /**
     * Updates the properties of an object in the current OneDrive account.
     *
     * @param string       $objectId   The unique ID of the object to update.
     * @param array|object $properties The properties to update. Default:
     *                                 array().
     *
     * @throws \Exception Thrown on I/O errors.
     */
    public function updateObject($objectId, $properties = array())
    {
        $properties = (object) $properties;
        $encoded    = json_encode($properties);
        $stream     = fopen('php://memory', 'w+b');

        if (false === $stream) {
            throw new \Exception('fopen() failed');
        }

        if (false === fwrite($stream, $encoded)) {
            throw new \Exception('fwrite() failed');
        }

        if (!rewind($stream)) {
            throw new \Exception('rewind() failed');
        }

        $this->apiPut($objectId, $stream, 'application/json');
    }

    /**
     * Moves an object into another folder.
     *
     * @param string      The unique ID of the object to move.
     * @param null|string The unique ID of the folder into which to move the
     *                    object, or null to move it to the OneDrive root
     *                    folder. Default: null.
     */
    public function moveObject($objectId, $destinationId = null)
    {
        if (null === $destinationId) {
            $destinationId = 'me/skydrive';
        }

        $this->apiMove($objectId, array(
            'destination' => $destinationId
        ));
    }

    /**
     * Copies a file into another folder. OneDrive does not support copying
     * folders.
     *
     * @param string      $objectId      The unique ID of the file to copy.
     * @param null|string $destinationId The unique ID of the folder into which
     *                                   to copy the file, or null to copy it to
     *                                   the OneDrive root folder. Default:
     *                                   null.
     */
    public function copyFile($objectId, $destinationId = null)
    {
        if (null === $destinationId) {
            $destinationId = 'me/skydrive';
        }

        $this->apiCopy($objectId, array(
            'destination' => $destinationId
        ));
    }

    /**
     * Deletes an object in the current OneDrive account.
     *
     * @param string $objectId The unique ID of the object to delete.
     */
    public function deleteObject($objectId)
    {
        $this->apiDelete($objectId);
    }

    /**
     * Fetches the quota of the current OneDrive account.
     *
     * @return object An object with the following properties:
     *                  'quota'     (int) The total space, in bytes.
     *                  'available' (int) The available space, in bytes.
     */
    public function fetchQuota()
    {
        return $this->apiGet('me/skydrive/quota');
    }

    /**
     * Fetches the account info of the current OneDrive account.
     *
     * @return object An object with the following properties:
     *                  'id'         (string) OneDrive account ID.
     *                  'first_name' (string) Account owner's first name.
     *                  'last_name'  (string) Account owner's last name.
     *                  'name'       (string) Account owner's full name.
     *                  'gender'     (string) Account owner's gender.
     *                  'locale'     (string) Account owner's locale.
     */
    public function fetchAccountInfo()
    {
        return $this->apiGet('me');
    }

    /**
     * Fetches the recent documents uploaded to the current OneDrive account.
     *
     * @return object An object with the following properties:
     *                  'data' (array) The list of the recent documents uploaded.
     */
    public function fetchRecentDocs()
    {
        return $this->apiGet('me/skydrive/recent_docs');
    }

    /**
     * Fetches the objects shared with the current OneDrive account.
     *
     * @return object An object with the following properties:
     *                    'data' (array) The list of the shared objects.
     */
    public function fetchShared()
    {
        return $this->apiGet('me/skydrive/shared');
    }
}
