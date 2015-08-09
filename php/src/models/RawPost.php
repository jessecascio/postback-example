<?php

namespace Model;

/**
 * Object represtation of raw post data
 */
class RawPost
{
	/**
	 * @var string
	 */
	private $method = '';

	/**
	 * @var string
	 */
	private $url    = '';

	/**
	 * @var array
	 */
	private $data   = [];

	/**
	 * @param array
	 */
	public function setRawPost(array $rawpost)
	{
		$this->method = isset($rawpost['endpoint']['method']) ? $rawpost['endpoint']['method'] : $this->method;
		$this->url    = isset($rawpost['endpoint']['url'])    ? $rawpost['endpoint']['url']    : $this->url;
		$this->data   = isset($rawpost['data'])               ? $rawpost['data']               : $this->data;
	}

	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param string
	 */
	public function setMethod($method)
	{
		$this->method = $method;
	}

	/**
	 * @param string
	 */
	public function setUrl($url)
	{
		$this->url = $url;
	}

	/**
	 * @param array
	 */
	public function setData(array $data)
	{
		$this->data = $data;
	}
}