<?php

require 'C:\wamp\www\acacia\secure_test\vendor\autoload.php';

use Academe\SagePay;

class RefundTest extends PHPUnit_Framework_TestCase{
	private $server;
	private $storage;

	protected function _getNewServer() {
		$server = new Academe\SagePay\Server();
		$storage = new Academe\SagePay\Model\TransactionPdo();
		$storage->setDatabase('sqlite:memory', '', '');
		$storage->createTable();
		$server->setTransactionModel($storage);
		$server->setPlatform('test');

		return $server;
	}

	public function setUp(){
		$this->server = $this->_getNewServer();
	}

	public function testRefundReturnsNewServer(){
		$this->server->setMain('PAYMENT', 'vendorx', '99.99', 'GBP', 'Store purchase', 'http://example.com/mycallback.php');
		$this->server->save();
		$originalVendorTxCode = $this->server->getField('VendorTxCode');

		// Make a new server to be the Refund
		$refund = $this->_getNewServer();
		$refund->createRefund($originalVendorTxCode, '99.99', 'Store Purchase Refund');

		$this->assertInstanceOf('Academe\SagePay\Shared', $refund);
	}

	public function testRefundHasCorrectFields(){
		// Use the Test server we have to create an original payment
		$this->server->setMain('PAYMENT', 'vendorx', '99.99', 'USD', 'Store purchase', 'http://example.com/mycallback.php');
		$this->server->save();
		$originalVendorTxCode = $this->server->getField('VendorTxCode');

		// Make a new server to be the Refund
		$refund = $this->_getNewServer();
		$refund->createRefund($originalVendorTxCode, '10', 'Store Purchase Refund');

		$this->assertEquals(10, $refund->getField('Amount'));
		$this->assertEquals('USD', $refund->getField('Currency'));
		$this->assertEquals('Store Purchase Refund', $refund->getField('Description'));
	}

	public function testRefundGeneratesGoodQueryString(){
		// Use the Test server we have to create an original payment
		$this->server->setMain('PAYMENT', 'vendorx', '99.99', 'USD', 'Store purchase', 'http://example.com/mycallback.php');
		$this->server->save();
		$originalVendorTxCode = $this->server->getField('VendorTxCode');

		// Make a new server to be the Refund
		$refund = $this->_getNewServer();
		$refund->createRefund($originalVendorTxCode, '10', 'Store Purchase Refund');
		$refund->save();

        $queryString = $refund->queryData(true, 'shared-refund');
        $queryParts = array();
        parse_str($queryString, $queryParts);

		$this->assertEquals($originalVendorTxCode, $queryParts['RelatedVendorTxCode']);
		$this->assertEquals('10', $queryParts['Amount']);
		$this->assertEquals($refund->getField('VendorTxCode'), $queryParts['VendorTxCode']);
		$this->assertEquals('USD', $queryParts['Currency']);
		$this->assertEquals('Store Purchase Refund', $queryParts['Description']);
	}
}