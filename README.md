# Client Library for SagePay Direct/Server - Protocol V3 #

Provides the functionality for Protocol 3 of the SagePay Server and SagePay Direct services. 
It purposefully does not fully support all
protocol v2 interface features (e.g. the non-XML basket, because the XML basket is a lot more flexible) but that could 
be added if people really desire it. V3 is truly a superset of the V2 protocol, so no functional features are lost.

## Main Requirements ##

A store is needed to track the transaction. This is handled by a descemndant class of Academe\SagePay\Model\TransactionAbstract.php.
This library allows you you use any store you like, e.g. an active database record, a WP post type,
a REST resource.

## Limitations ##

The first working release of this library will focus on paying PAYMENT transactions. It has not been
tested with repeating transactions or DEFERRED or AUTHENTICATE transaction types, or
the myriad other services. However, these are all being worked on.

This library is only handling "SagePay Server" at present. This service pushes details of the transaction to
SagePay via a back-channel, then sends the user to SagePay to enter their credit card details. Credit card
details do not have to be taken on your own site, and that helps immensely with PCI accreditation. You also
do not need a SSL certificate beyond a simple one for encrypting address details as they are entered.

"SagePay Direct" allows you to keep the user on your own site, while taking payment details at least. 
You take all credit card details on your site
and send the full payment details via a back-channel to SagePay. You need a good SSL certificate, and PCI
certification is a lot more involved, since you are directly handling end-user credit card details.
This is not really as big an advantage as it first appears, as you still need to send visitors to other
sites for 3DSecure authorisation and PayPal authentication. These sites can all be embdded into an
iframe to improve the user experience, but that also applies to SagePay Server.
This library does not support this service at present, though it is being worked on.

## Status ##

This library is being actively worked on. Having said that, is *is* production-ready and is in service now
for SagePay Server. SagePay Direct is still being developed, which will happen after a little refactoring.
The intention is for a back-end library for SagePay
protocol version 3, that can use any storage mechanism you like and does not have side-effects
related to input (i.e. does not read POST behind your back, so your application controls all
routing and input validation).

So far there is a storage model abstract, with an example PDO storage implementation. There are
models for the basket, addresses, customers, and surcharges.

[This wiki page](https://github.com/academe/SagePay/wiki/List-of-Messages) lists the messages that will
ultimately be supported by the library.

## Installation ##

This library does not depend on any other composer libraries at present. If using composer, it
can be installed like this (in composer.json):

    {
        "repositories": [
            {
                "type": "vcs",
                "url": "https://github.com/academe/SagePay"
            }
        ],
        "require": {
            "php": ">=5.3.0",
            "academe/sagepay": "dev-master"
        }
    }

Or if working on a clone of this repository in in vendor/sagepay:

    {
        "autoload": {
            "psr-0": "Academe\\SagePay": "vendor/sagepay/src"
        }
    }

The official releases are also available on [Packageist](https://packagist.org/packages/academe/sagepay)
and so through [composer](http://getcomposer.org/).

## What else do you need? ##

This library handles the back-end processing only. You will need:

* Front-end forms to capture the user's name and address (not required for SagePay Direct).
* Validation on those forms to act as a gatekeeper before we sent those details to SagePay.
* Routeing and a handler for the notification callback that SagePay will perform (the handler is more complex
  for SagePay Direct, as you need to handle more of the protocol on your site).
* A MySQL database or an extension to the Transaction model for persisting the transaction data.
  The transaction data can be stored anywhere you like, but a simple PDO extension, only tested on MySQL, is
  built in for convenience.

Some more detailed examples of how this could work, will follow later. If you want to wrap this
library up in a more diverse library, such as [OmniPay](https://github.com/adrianmacneil/omnipay), then this
would be a good start - it handles all the nuances of SagePay and so should be easier to incorporate into
a multi-gateway payment site. IMO OmniPay is too monolithic, a single library that aims to be the jack of
all trades, but as a framework to pull together many payment gateways into one unified interface, is a
great idea. But that's an argument for another day. Please let me know what you think.

## Usage ##

Very roughly, registering a [payment] transaction request will look like this:

    // In all the code examples here, I will assume a PSR-0 autoloader is configured.
    // e.g. for composer this may be included like this, taking the path to the vendor
    // directory into account:
    
    require 'vendor/autoload.php';

    // This just half the process. This registers a payment request with the gateway.
    
    // Create the Server registration object.
    
    $server = new Academe\SagePay\Server();
    
    // Create a storage model object.
    // A basic PDO storage is provided, but just extend Model\Transaction and use your own.
    // Your framework may have active record model, or you may want to use WordPress post types, for example.
    // You can write your own transaction storage model, perhaps storing the transaction data in a custom
    // post type in WordPress, or a database model in your framework. This TransactionPdo model is just
    // a usable example that comes with the library.
    
    $storage = new Academe\SagePay\Model\TransactionPdo();
    $storage->setDatabase('mysql:host=localhost;dbname=MyDatabase', 'MyUser', 'MyPassword');
    
    // Within WordPress, setting the database details looks like this:
    
    $storage->setDatabase('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
    
    // Or alternatively use the storage model TransactionPdoWordpress and have the database details
    // set for you automatically:
    
    $storage = new Academe\SagePay\Model\TransactionPdoWordpress(); // No need to call setDatabase()
    
    // Inject the storage object.
    
    $server->setTransactionModel($storage);
    
    // If you want to create a table ("sagepay_transactions" by default) for the PDO storage, do this.
    // The table will be created from the details in Metadata\Transaction and should provide a decent
    // out-of-the-box storage to get you up and running. You could execute this in the initialisation
    // hook of a plugin, assuming you are not using a custom post type to track the transactions.
    
    $storage->createTable();
    
    // The PDO storage table may need upgrading for new releases. Call this method to do that:
    
    $storage->updateTable();
    
    // Note that both createTable() and updateTable() are both specific to the PDO storage model.
    // You may store your data elsewhere and have your own way of setting up structures and storage.
    // For example, the transactions may be stored in a model in a framework that has its own way
    // to migrate database structures during releases and upgrades.
        
    // Set the main mandatory details for the transaction.
    // We have: payment type, vendor name, total amount, currency, note to display to user, callback URL.
    
    $server->setMain('PAYMENT', 'vendorx', '99.99', 'GBP', 'Store purchase', 'http://example.com/mycallback.php');
    
    // Indicate which platform you are connecting to - test or live.
    
    $server->setPlatform('test');
    
    // Set the addresses.
    // You can just set one (e.g. billing) and the other will automatically mirror it. Or set both.
    
    $billing_addr = new Academe\SagePay\Model\Address();
    $billing_addr->setField('Surname', 'Judge');
    $billing_addr->setField('Firstnames', 'Jason');
    $billing_addr->setField('Address1', 'Some Street Name');
    $billing_addr->setField('City', 'A City Name');
    // etc.
    $server->setBillingAddress($billing_addr);
    
    // Set optional stuff, including customer details, surcharges, basket.
    // Here is an example for the basket. This is a very simple example, as SagePay 3.0
    // can support many more structured data items and properties in the basket.
    // The currency needs to be set as it affects how the monetory amounts are formatted.
    
    $basket = new Academe\SagePay\Model\Basket();
    $basket->setCurrency('GBP');
    $basket->setDelivery(32.50, 5);
    $basket->addSimpleLine('Widget', 4.00, 3, 0.75, 3.75);
    $server->setBasketModel($basket);
    
    // Send the request to SagePay, get the response, The request and response will also
    // be saved in whatever storage you are using.
    
    $server->sendRegistration();

The response will provide details of what to do next: it may be a fail, or give a SagePay URL to jump to, or
just a simple data validation error to correct. If `$server->getField('Status')` is "OK" then redirect
the user to `$server->getField('NextURL')` otherwise handle the error.

SagePay is very strict on data validatin. If a postcode is too long, or an address has an invalid character
in, then it will reject the registration, but will not be very clear exactly why it was rejected, and
certainly not in a form that can be used to keep the end user informed. For this reason, do not just
throw an address at this library, but make sure you validate it according to SagePay validation rules, 
and provide a pre-submit form for the user to make corrections (e.g. to remove an invalid character from
an address field - something that may be perfectly valid in the framework that the address came from,
and may be perfectly valid in other payement gateways). Just get used to it - this is the Sage way - always
a little bit clunky in some unexpected ways.

The field metadata in this library provides information on the validation rules.
The library should validate everything before it goes to SagePay, but also those rules should be available
to feed into the framework that the end user interacts with. Work is still to be done here.

Once a transaction registration is submitted successfuly, and the user is sent to SagePay to enter their
card details, SagePay will send the result to the callback URL. This is easily handled, with mycallback.php
looking something like this:

    // Gather the POST data.
    // For some platforms (e.g. WordPress) you may need to do some decoding of the POST data.
    
    $post = $_POST;
    
    // Set up the transaction model, same as when registering. Here is a slightly shorter-hand version.
    
    $server = new Academe\SagePay\Server();
    $server->setTransactionModel(new Academe\SagePay\Model\TransactionPdo())
        ->setDatabase('mysql:host=localhost;dbname=MyDatabase', 'MyUser', 'MyPassword'');
    
    // Handle the notification.
    // The final URL sent back, which is where the user will end up. We are also passing the
    // status with the URL for convenience, but don't rely on looking at that status to
    // determine if the payment was successful - a user could fake it.
    
    $result = $server->notification(
        $post, 
        'http://example.com/mysite/final.php?status={{Status}}'
    );
    
    // Other actions can be performed here, based on what we find in `$server->getField('Status')`
    // For example, you may want to inform an application that an invoice has been paid.
    // You may also want to send the user an email at this point (to `$server->getField('CustomerEMail')`
    
    // Return the result to SagePay.
    // Do not output *anything* else on this page. SagePay is expecting the contents of $result *only*.
    // If you are calling up other code here to take action on the transaction, then it may be worth
    // using ob_start()/ob_end_clean() to catch and discard any output that may be generated.
    
    echo $result;
    
    exit();
    
Now, at this point the user will be sent back to mysite/final.php

Here the user needs to be informed about the outcome, and that will depend on the result and contents
of the transaction. The page will need the VendorTxCode to get hold of the transaction like this:

    // Set up the transaction model, same as when registering. Here is a slightly shorter-hand version.
    
    $server = new Academe\SagePay\Server();
    $server->setTransactionModel(new Academe\SagePay\Model\TransactionPdo())
        ->setDatabase('mysql:host=localhost;dbname=foobar', 'myuser', 'mypassword');

    // Fetch the transaction from storage.
    
    $server->findTransaction($VendorTxCode);
    
    // Look at the result and take it from there.
    
    $status = $server->getField('Status');
    
    if ($server->isPaymentSuccess()) {
        echo "Cool. Your payment was successful.";
    } elseif ($status == 'PENDING') {
        echo "Your payment has got delayed while being processed - we will email you when it finally goes through.";
    } else {
        echo "Whoops - something went wrong here. No payment has been taken.";
    }
    
The question is where the VendorTxCode comes from. It could be passed in via the URL by setting the final
URL in the callback page to `mysite/final.php?txcode={{VendorTxCode}}` However, you may not want that ID
to be exposed to the user. Also this final page would be permanently active - the transaction code will
always be there in storage until it is cleared out.

You may save VendorTxCode in the session when the payment registration is first
made, and then retrieve it (and delete it) on the final page. That way this becomes a once-only access to the
transaction result. If the user pays for several transactions at the same time, then the result of the
transaction started first will be lost, but the processing should otherwise work correctly.

The PENDING status is one to watch. For that status, the transaction is neither successful nor failed. It is
presumably on some queue at some bank somewhere to be processed. When it is processed, the callback page
will be called up by SagePay with the result. This is important to note, because the user will not be
around to see that happen. So if the user needs to be notified by email, or the transaction result needs
to be tied back to some action in the application (e.g. marking an invoice as paid or a membership as renewed)
then it MUST be done in the callback page. Do *not* rely on this kind of thing to be done in the final.php
page that the user is sent to.

You can make use of the `CustomerData` field in the transaction for linking the payment to a resource in
the application to be actioned.

## A Note About Currencies ##

This library will support any ISO 4217 currency, identified by its three-character code. However, the
merchant account connected to the SagePay account will normally only support a subset of those currencies. This
page lists the current merchant accounts and the currencies they support:

http://www.sagepay.com/help/faq/merchant_number_format

Some merchant accounts support dozens of currencies, and some only a handful. A SagePay account can be set up
to further restrict the list that the merchant account supports.

There is no server or direct API that will list the supported currencies. The Reporting and Admin API does
provide getCurrencies() to list the currencies that the vendor account supports. This library does not yet
support the Reporting and Admin API, but it is something that is likely to be added. Here is a library
that talks to the Reporting and Admin API now:

https://github.com/trashofmasters/sagepayadminapi-php

By supporting a currency for payments, it means that payments can be taken in that currency. A shop will
often be based in a single country and support just that local currency. If your shop supports multiple
currencies, then it is your responsibility to set the correct prices in each currency according to the
exchange rates. For example, if you are a UK based company and you choose to sell a widget to 20USD,
then it will be the banks and card operators that handle the exchange. You won't know how much that
20USD will be worth in GBP until it hits your bank account.

A shop selling a product at 10 USD (and only USD) will still accept payments from people in other countries. 
In that case
it will be the purchaser's card supplier that will calculate the amount to be paid in their local currency
to ensure the shop receives exactly 10 USD.

