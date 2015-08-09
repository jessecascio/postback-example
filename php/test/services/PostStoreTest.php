<?php

class PostStoreTest extends PHPUnit_Framework_TestCase
{	
	private $storage;

	private $poststore;

	public function setUp()
	{
		$this->storage   = \Phake::mock('Predis\Client');
		$this->poststore = new Service\PostStore($this->storage);
	}

	public function testValidPost()
	{
		$rawpost   = new Model\RawPost();
		$rawpost->setMethod('POST');
		$rawpost->setUrl('http://google.com');
		$rawpost->setData([1,2]);

		$this->poststore->setRawPost($rawpost);

		$this->assertTrue($this->poststore->isValid());
	}

	public function testInValidMethod()
	{
		$rawpost   = new Model\RawPost();
		$rawpost->setMethod('FOO');
		$rawpost->setUrl('http://google.com');
		$rawpost->setData([1,2]);

		$this->poststore->setRawPost($rawpost);

		$this->assertFalse($this->poststore->isValid());
	}

	public function testInValidUrl()
	{
		$rawpost   = new Model\RawPost();
		$rawpost->setMethod('GET');
		$rawpost->setUrl('');
		$rawpost->setData([1,2]);

		$this->poststore->setRawPost($rawpost);

		$this->assertFalse($this->poststore->isValid());
	}

	public function testInValidData()
	{
		$rawpost   = new Model\RawPost();
		$rawpost->setMethod('FOO');
		$rawpost->setUrl('http://google.com');
		$rawpost->setData([]);

		$this->poststore->setRawPost($rawpost);

		$this->assertFalse($this->poststore->isValid());
	}

	public function testValidPostStore()
	{
		$rawpost   = new Model\RawPost();
		$rawpost->setMethod('GET');
		$rawpost->setUrl('http://google.com');
		$rawpost->setData([1,2]);

		$this->poststore->setRawPost($rawpost);

		$this->assertTrue($this->poststore->storePosts());
		\Phake::verify($this->storage, \Phake::times(1))->pipeline(\Phake::anyParameters());
	}

	public function testInvalidPostStore()
	{
		$rawpost   = new Model\RawPost();
		$rawpost->setMethod('GET');
		$rawpost->setUrl('');
		$rawpost->setData([1,2]);

		$this->poststore->setRawPost($rawpost);

		$this->assertFalse($this->poststore->storePosts());
	}
}
