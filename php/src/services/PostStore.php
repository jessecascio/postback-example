<?php

namespace Service;

use Predis;
use Model;

/**
 * Service for storing postback objects into data store
 */
class PostStore
{	
	/**
	 * @var Predis\Client
	 */
	private $storage;

	/**
	 * @var Model\RawPost
	 */
	private $rawpost;

	/**
	 * @var array
	 */
	private $methods = ['POST', 'GET'];

	/**
	 * @var string
	 */
	private $error = '';

	/**
	 * @param Predis\Client
	 */
	public function __construct(Predis\Client $storage)
	{
		$this->storage = $storage;
	}

	/**
	 * @return string
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * @param Model\RawPost
	 */
	public function setRawPost(Model\RawPost $rawpost) 
	{
		$this->rawpost = $rawpost;
	}

	/**
	 * @return bool
	 */
	public function isValid()
	{
		if (is_null($this->rawpost)) {
			return false;
		}

		if (!in_array($this->rawpost->getMethod(), $this->methods)) {
			$this->error = "Invalid Method";
			return false;
		}

		// may want additional URL checking
		if (!trim($this->rawpost->getUrl())) {
			$this->error = "Invalid Url";
			return false;
		}

		if (!is_array($this->rawpost->getData()) || !count($this->rawpost->getData())) {
			$this->error = "Invalid Data";
			return false;
		}

		return true;
	}

	/**
	 * Batch store
	 */
	public function storePosts()
	{
		if (!$this->isValid()) {
			return false;
		}

		try {
			// would break this up i.e. only pipeline 5000 at a time
			$this->storage->pipeline(function ($pipe) {
				foreach ($this->rawpost->getData() as $data) {
					$postback = [];

					$postback['method'] = $this->rawpost->getMethod();
					$postback['url']    = $this->rawpost->getUrl();
					$postback['data']   = $data;

					$pipe->lpush('job-queue', json_encode($postback));
				}
			});		
		} catch (\Exception $e) {
			$this->error = $e->getMessage();
			return false;
		}
		
		return true;
	}
}