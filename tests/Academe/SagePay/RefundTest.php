<?php

require 'C:\wamp\www\acacia\secure_test\vendor\autoload.php';

use Academe\SagePay;

class RefundTest extends PHPUnit_Framework_TestCase{
	private $server;
	private $storage;

	public function setUp(){
		$server = new Academe\SagePay\Server();
		$storage = new Academe\SagePay\Model\TransactionPdo();
		$storage->setDatabase('sqlite:memory', '', '');

		$server->setTransactionModel($storage);
		$server->setPlatform('test');

		$this->server = $server;
		$this->storage = $storage;
	}

	public function testRefundReturnsNewServer(){
		$this->server->setMain('PAYMENT', 'vendorx', '99.99', 'GBP', 'Store purchase', 'http://example.com/mycallback.php');
		$refund = $this->server->refund('99.99', 'Refunded Store Purchase');
		$this->assertInstanceOf('Academe\SagePay\Shared', $refund);
	}

	public function testRefundHasCorrectFields(){
		$this->server->setMain('PAYMENT', 'vendorx', '99.99', 'USD', 'Store purchase', 'http://example.com/mycallback.php');
		$refund = $this->server->refund(10, 'Refunded Store Purchase');
		$this->assertEquals(10, $refund->getField('Amount'));
		$this->assertEquals('USD', $refund->getField('Currency'));
		$this->assertEquals('Refunded Store Purchase', $refund->getField('Description'));
	}
}