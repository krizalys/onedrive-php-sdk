<?php

namespace Krizalys\Onedrive\Proxy;

use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Stream;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\DriveItem;
use Microsoft\Graph\Model\UploadSession;

class UploadSessionProxy extends EntityProxy {
	
	private $name;
	private $resource;
	private $options;
	
	const MIN_CHUNK_SIZE  = 320 * 1024; //minimal chunk size 320 KiB
	const MAX_CHUNK_SIZE  = 60 * 1024 * 1024; //minimal chunk size 60 MiB
	
	/**
     * Constructor.
     *
     * @param Graph
     *        The graph.
     * @param UploadSession
     *        The upload session.
	 * @param string Name
	 *        The name.
	 * @param string|resource|\GuzzleHttp\Psr7\Stream
	 *        The content.
	 * @param array options
	 *        The options.
     */
	public function __construct(Graph $graph, UploadSession $uploadSession, string $name, $resource, array $options = [])
	{
		parent::__construct($graph, $uploadSession);
		$this->name = $name;
		$this->resource = $resource;
		$this->options = $options;
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
        $uploadSession = $this->entity;

        switch ($name) {
            case 'expirationDateTime':
                return $uploadSession->getExpirationDateTime();
				
			case 'nextExpectedRanges':
                return $uploadSession->getNextExpectedRanges();
				
			case 'uploadUrl':
                return $uploadSession->getUploadUrl();
			
            default:
                return parent::__get($name);
        }
    }
	
	/**
     * Checks options array for key.
     *
     * @param string $key
     *        The key.
     *
     * @return bool
     *         The boolean value.
     */
	private function isOptionExists(string $key)
	{
		return array_key_exists($key, $this->options);
	}
	
	/**
     * Gets value corresponding to key
	 * from options array
     *
     * @param string $key
     *        The key.
     *
     * @return mixed
     *         The value.
     */
	private function getOption(string $key)
	{
		return $this->options[$key];
	}
	
	/**
     * Uploads file contents as chunks.
	 *
     * @todo Support retry on error while uploading chunks.
     */
	public function run()
	{
		
		$resourceStream =  $this->resource instanceof Stream ? $this->resource : Psr7\stream_for($this->resource);
		$bytes = "";
		$bytesRead=0;
		$totalSize = 0;
		
		//Get the resource size from options array. If not available, calculate from stream contents
		$totalSize = $this->isOptionExists("streamSize") ? $this->getOption("streamSize") : strlen($resourceStream ->getContents());
		$resourceStream->rewind(); //rewind stream handle to start of stream
		
		//Default chunk size to MIN_CHUNK_SIZE
		$chunkSize = UploadSessionProxy::MIN_CHUNK_SIZE;
		
		//If user defined chunk size exists in options array get the size matching the range 320 KiB < chunkSize < 60 MiB
		if($this->isOptionExists("chunkSize")) {
			$chunkSize = min(max($chunkSize, $this->getOption("chunkSize")), UploadSessionProxy::MAX_CHUNK_SIZE);
		}
		
		while (!$resourceStream->eof()) {
			
			$bytes = $resourceStream->read($chunkSize);
			$bytesLength = strlen($bytes);
			
			//initialise bytes read, and interval range
			$byteIntervalStart=$bytesRead;
			$bytesRead += $bytesLength;
			$byteIntervalEnd=$bytesRead-1;
			
			//upload headers containing bytes Content length and range
			$sessionHeaders = [
				"Content-Length" => $bytesLength,
				"Content-Range" => "bytes $byteIntervalStart-$byteIntervalEnd/$totalSize",
			];
			
			//create a PUT request for chunks
			$response = $this
				->graph
				->createRequest('PUT', $this->uploadUrl)
				->addHeaders($sessionHeaders)
				->attachBody($bytes)
				->execute();
			
			$status = $response->getStatus();
			
			if($status != 201 && $status != 202){
				throw new \Exception("Unexpected status code produced by 'PUT $endpoint': $status");
			}
			
			if($status === 201){
				//resource created in onedrive return response object as DriveItem
				return $response->getResponseAsObject(DriveItem::class);
			}
			
		}
		
		return new self($this->graph, $this->uploadSession, $this->name, $this->resource, $this->options);
	}
}

?>
