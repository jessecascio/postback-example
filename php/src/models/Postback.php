<?php

namespace Model;

class PostBack
{
	/**
	 * @param string
	 */
	public $method = 'GET';

	/**
	 * @param string
	 */
	public $url    = '';

	/**
	 * @param array
	 */
	public $data   = [];
}