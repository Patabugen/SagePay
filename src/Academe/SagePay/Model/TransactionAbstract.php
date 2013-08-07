<?php

/**
 * Used to store, retrieve and update transaction details between pages.
 * Add your own concrete storage methods, such as active record or a direct
 * database table.
 *
 * Data put into these fields should be truncated and formatted correctly.
 * That includes numbers to the correct number of decimal points, text fields
 * truncated to their maximum length (take ml features into account), and
 * charactersets converted (that last one should probably be done in the final
 * transport stage - keep the characterset here in one that the application
 * understands).
 */

namespace Academe\SagePay\Model;

abstract class TransactionAbstract
{
    /**
     * The fields we need to (and would like to) track for a transaction.
     * Not all must be saved to complete a transaction, but all would be useful
     * for debugging and auditing purposes.
     * TODO: could all this go into an external metadata file, with details of 
     * optionality, validation, default values and source of data? This will also
     * cut down on data in other places, such as Register::queryData()
     */

    protected $fields = array(
        // The base protocol version.
        // A concrete implementation may extend the version if there are minor updates.
        // Supplied in transaction registration.
        'VPSProtocol' => '3.00',

        // Can be PAYMENT, DEFERRED or AUTHENTICATE.
        // Also some transaction types share with SagePay Difrect (REFUND, RELEASES, ABORTS, REPEATS)
        // Supplied in transaction registration.
        'TxType' => 'PAYMENT',

        // The vendor login name.
        // Supplied in transaction registration.
        'Vendor' => '',

        // Tranaaction ID generated by the vendor, up to 40 characters.
        // Supplied in transaction registration.
        'VendorTxCode' => '',

        // The total transaction amount (0.01 to 100,000.00).
        // Decimal places will be formatted according to the currnecy.
        // Supplied in transaction registration.
        'Amount' => '0',

        // The currency for the transaction (GBP, EUR or USD).
        // Supplied in transaction registration.
        'Currency' => 'GBP',

        // Overall description, up to 100 characters. Displayed to use on SagePay screens.
        // Supplied in transaction registration.
        'Description' => '',

        //
        // Billing contact details.
        //

        // Surname of the billed individual. Max 20 characters.
        // Supplied in transaction registration.
        'BillingSurname' => '',

        // First name(s) of the billed individual. Max 20 characters.
        // Supplied in transaction registration.
        'BillingFirstnames' => '',

        // First line of the billed address. Max 100 characters.
        // Supplied in transaction registration.
        'BillingAddress1' => '',

        // Second line of the billed address. Max 100 characters.
        // This is the only fully optional part of the billing address.
        // Supplied in transaction registration.
        'BillingAddress2' => null,

        // City name of the billed address. Max 40 characters.
        // Supplied in transaction registration.
        'BillingCity' => '',

        // Postal code of the billed address. Max 10 characters.
        // This is optional only for some countries that do not use postal codes.
        // Supplied in transaction registration.
        'BillingPostCode' => '',

        // ISO3166-1 alpha-2 code for the billed address country. Exactly 2 characters.
        // Supplied in transaction registration.
        'BillingCountry' => '',

        // Two-character US state code of the billed address. Max 2 characters.
        // This must only be completed only for US addresses (CHECKME: may be mandatory for US address).
        // Supplied in transaction registration.
        'BillingState' => null,

        // Phone number for the billed address country. Max 20 characters.
        // Optional.
        // Supplied in transaction registration.
        'BillingPhone' => null,

        //
        // Delivery contact details.
        //

        // Surname of the delivery individual. Max 20 characters.
        // Supplied in transaction registration.
        'DeliverySurname' => '',

        // First name(s) of the delivery individual. Max 20 characters.
        // Supplied in transaction registration.
        'DeliveryFirstnames' => '',

        // First line of the delivery address. Max 100 characters.
        // Supplied in transaction registration.
        'DeliveryAddress1' => '',

        // Second line of the delivery address. Max 100 characters.
        // This is the only fully optional part of the billing address.
        // Supplied in transaction registration.
        'DeliveryAddress2' => '',

        // City name of the delivery address. Max 40 characters.
        // Supplied in transaction registration.
        'DeliveryCity' => '',

        // Postal code of the delivery address. Max 10 characters.
        // This is optional only for some countries that do not use postal codes.
        // Supplied in transaction registration.
        'DeliveryPostCode' => '',

        // ISO3166-1 alpha-2 code for the delivery address country. Exactly 2 characters.
        // Supplied in transaction registration.
        'DeliveryCountry' => '',

        // Two-character US state code of the delivery address. Max 2 characters.
        // This must only be completed only for US addresses (CHECKME: may be mandatory for US address).
        // Supplied in transaction registration.
        'DeliveryState' => null,

        // Phone number for the delivery address country. Max 20 characters.
        // Optional.
        // Supplied in transaction registration.
        'DeliveryPhone' => null,

        //
        // Other details.
        //

        // Email address for the customer. Max 255 characters.
        // Optional.
        // Supplied in transaction registration.
        'CustomerEMail' => null,

        // We will not use the old-style Basket field, but use the BasketXML field instead.
        //'Basket' = '',

        // Indicate whether Gift Aid is allowed, provided the account if Gift Aid enabled.
        // 0=No; 1=Yes
        // Exactly 1 character.
        // Optional.
        // Supplied in transaction registration.
        'AllowGiftAid' => null,

        // Indicate whether AVS/CV2 rules should be applied.
        // 0=Yes, if enabled; 1=Yes forced; 2=No forced; 3=Yes, force check but don't apply rules.
        // Exactly 1 character.
        // Optional.
        // Supplied in transaction registration.
        'ApplyAVSCV2' => null,

        // Indicate whether 3DSecure rules should be applied.
        // 0=Yes, if enabled; 1=Yes forced; 2=No forced; 3=Yes, check if possible but always get auth code.
        // Exactly 1 character.
        // Optional.
        // Supplied in transaction registration.
        'Apply3DSecure' => null,

        // PayPal repeating bill indicator.
        // 0=normal single transaction; 1=first in a series of REPEAT transactions.
        // Exactly 1 character.
        // Optional.
        // Supplied in transaction registration.
        'BillingAgreement' => null,

        // Indicate which merchant account to use for non-PayPal transactions.
        // E=e-commerce; M=mail/telephone order; C=continuous authority.
        // Exactly 1 character.
        // Optional.
        // Supplied in transaction registration.
        'AccountType' => null,

        // Indicate whether a token should be generated for PAYMENT, AUTHENTICATE or DEFERRED
        // transaction types.
        // 0=no token; 1=generate token.
        // Exactly 1 character.
        // Optional.
        // Supplied in transaction registration.
        'CreateToken' => null,

        // Shopping basket, formatted as XML.
        // Max 20,000 character.
        // Optional.
        // Supplied in transaction registration.
        'BasketXML' => null,

        // Supply details about the customer in XML format.
        // Max 2,000 character.
        // Optional.
        // Supplied in transaction registration.
        'CustomerXML' => null,

        // Supply details of required transaction surcharges in XML format.
        // Max 800 character.
        // Optional.
        // Supplied in transaction registration.
        'SurchargeXML' => null,

        // Free-format data to display in the SagePay administration pages.
        // Max 200 character.
        // Optional.
        // Supplied in transaction registration.
        'VendorData' => null,

        // Data returned from SagePay below.

        // The status returned from the transaction registration POST.
        // Max 14 characters.
        // Values are: OK, MALFORMED, INVALID or ERROR.
        // Also set by the callback function, which has additional statuses:
        // Values are: OK, NOTAUTHED, ABORT, REJECTED, AUTHENTICATED, REGISTERED, PENDING, ERROR.
        'Status' => null,

        // The status detail returned from the transaction registration POST.
        // Max 255 characters.
        // A list of status details can be found here, and may be parsable, so we could at
        // least have a list of some detail messages that can be presented to the end user:
        // http://www.sagepay.com/help/systemmessageindex
        // However, that list is not complete. I have already encountered:
        // "3021 : The Basket format is invalid."
        'StatusDetail' => null,

        // The StatusDetail split up into a code and a message.
        // The code is the only reliable item available to programmatically point to which
        // field has an issue or to provide appropriate corrective hints to the user.
        // A lookup table of codes agaist classes of error and field names would be a good
        // addition...
        'StatusDetailMessage' => null,
        'StatusDetailCode' => null,

        // The URL the user should be sent to. Provided by SagePay.
        'NextURL' => null,

        // The SagePay transaction ID returned from the transaction registration POST.
        // Alphnumeric 38 characters.
        // Only present if Status is 'OK'.
        'VPSTxId' => null,

        // The SagePay security key returned from the transaction registration POST.
        // Used when generating an md5 hash in the notification POST (kind of a private key)
        // and used to detect tampering of the notification message.
        // Alphanumeric 10 characters.
        // Only present if Status is 'OK'.
        'SecurityKey' => null,

        // SagePay unique authorisation code for a successful authorisation.
        // Sent with the notification POST.
        // Datatype: long integer.
        'TxAuthNo' => null,

        // AVS/CV2 check result.
        // Sent with the notification POST.
        // Values are: ALL MATCH, SECURITY CODE MATCH ONLY, ADDRESS MATCH ONLY,
        // NO DATA MATCHES or DATA NOT CHECKED
        // Max 50 characters.
        'AVSCV2' => null,

        // Cardhoolder address check result.
        // Sent with the notification POST.
        // Values are: NOTPROVIDED, NOTCHECKED, MATCHED, NOTMATCHED
        // Max 20 characters.
        'AddressResult' => null,

        // Cardhoolder postcode check result.
        // Sent with the notification POST.
        // Values are: NOTPROVIDED, NOTCHECKED, MATCHED, NOTMATCHED
        // Max 20 characters.
        'PostCodeResult' => null,

        // Cardhoolder CV2 check result.
        // Sent with the notification POST.
        // Values are: NOTPROVIDED, NOTCHECKED, MATCHED, NOTMATCHED
        // Max 20 characters.
        'CV2Result' => null,

        // Indicates whether Gift Aid was selected.
        // Sent with the notification POST.
        // Values are: 0=No; 1=Yes.
        'GiftAid' => null,

        // Result of the 3DSecure check.
        // Sent with the notification POST.
        // Values are: OK, NOTCHECKED, NOTAVAILABLE, NOTAUTHED, INCOMPLETE, ERROR, ATTEMPTONLY.
        // Max 50 characters.
        '3DSecureStatus' => null,

        // The encoded result code from the 3D-Secure checks.
        // Sent with the notification POST.
        // Max 32 characters.
        'CAVV' => null,

        // PayPal only: whether the address status was confirmed.
        // Sent with the notification POST.
        // Values are: NONE, CONFIRMED or UNCONFIRMED.
        // Max 20 characters.
        'AddressStatus' => null,

        // PayPal only: whether the payer status was confirmed.
        // Sent with the notification POST.
        // Values are: VERIFIED or UNVERIFIED.
        // Max 20 characters.
        'PayerStatus' => null,

        // The card type used.
        // Sent with the notification POST.
        // Values are: VISA, MC, MCDEBIT, DELTA, MAESTRO, UKE, AMEX, DC,
        // JCB, LASER, PAYPAL, EPS, GIROPAY, IDEAL, SOFORT, ELV
        // Max 15 characters.
        'CardType' => null,

        // Last four digits of the card used for payment.
        // Sent with the notification POST.
        // Max 4 characters.
        'Last4Digits' => null,

        // UPPER CASE MD5 hash of data in the notifictions POST plus the SecurityKey sent with the
        // transaction registration. Concatenated fields are:
        // VPSTxId + VendorTxCode + Status + TxAuthNo + VendorName+ AVSCV2 + SecurityKey
        // + AddressResult + PostCodeResult + CV2Result + GiftAid + 3DSecureStatus + CAVV
        // + AddressStatus + PayerStatus + CardType + Last4Digits + DeclineCode
        // + ExpiryDate + FraudResponse + BankAuthCode
        //
        // Sent with the notification POST.
        // Max 100 characters.
        'VPSSignature' => null,

        // Response from ReD.
        // Sent with the notification POST.
        // Values are: ACCEPT CHALLENGE, DENY or NOTCHECKED.
        // Max 10 characters.
        'FraudResponse' => null,

        // The value of any surcharge added to the transaction.
        // Sent with the notification POST.
        // Numeric decimal.
        'Surcharge' => null,

        // Authorisation code returned by the bank.
        // Sent with the notification POST.
        // Numeric max 6 digits.
        'BankAuthCode' => null,

        // Decline code from the bank. Values have meaning specific to that bank.
        // Sent with the notification POST.
        // Numeric max 2 digits.
        'DeclineCode' => null,

        // Expiry date of the card used (MMYY).
        // Sent with the notification POST.
        // Numeric 4 digits.
        'ExpiryDate' => null,

        // The token generated by SagePay in response to the registration phase.
        // Sent with the notification POST.
        // 38 characters GUID.
        'Token' => null,
    );


    /**
     * The reference ID of the partner that referred the vendor to SagePay.
     * Max 40 character.
     * Optional.
     * Supplied in transaction registration.
     */

    protected $ReferrerID = 'academe';

    /**
     * The ISO639-2 2-digit code the language to use.
     * Will default to the user's browser if not supplied.
     * Note: 2-digit codes are not sufficient to cover all languages, so for future-proofing
     * this ought to be used 3-digit codes.
     * Exactly 2 character.
     * e.g. en=English; fr-French; de=German; es=Spanish
     * Optional.
     * Supplied in transaction registration.
     */
    protected $Language = null;

    /**
     * Referenced to the website transaction came from.
     * Max 100 characters.
     * Optional.
     * Supplied in transaction registration.
     */

    protected $Website = null;

     /**
     * The notificatinon URL that SagePay will be using to notify results to the vendor site.
     * Supplied in transaction registration.
     */

    protected $NotificationURL = '';

    /**
     * Indicates the layout format (complete page or simple).
     * Values: NORMAL or LOW
     * Supplied in transaction registration.
     */

    protected $Profile = null;

    //
    //abstract public function xxx() {}

    /**
     * Get the SagePay protocol version.
     */

    public function getProtocolVersion()
    {
        return $this->get_field('VPSProtocol');
    }

    /**
     * Get a field value.
     * Check the model field array first, then the class properties.
     */

    public function getField($name)
    {
        if (isset($this->fields[$name])) {
            return $this->fields[$name];
        }

        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        return null;
    }

    /**
     * Set a field value.
     * Check if the field exists in the model field array first, then the class properties.
     * Force the value into the field data array or a property using $force='data' or $force='property'
     */

    public function setField($name, $value, $force = null)
    {
        // If setting the StatusDetail, then split this up into a message and code.
        // Status messages may come from other sources, so be prepared that a StatusDetail
        // value is not a simple "{code} : {message}"
        // There us also no documentation that states this format will not change.

        if ($name == 'StatusDetail') {
            // Split at the first colon.
            $split = explode(':', $value, 2);

            if (count($split) == 2) {
                list($code, $message) = $split;

                $code = trim($code);
                $message = trim($message);

                // The code should be numeric (hopefully, but who knows without SagePay
                // providing definitive docuementation?).

                if (is_numeric($code)) {
                    $this->setField('StatusDetailCode', $code);
                    $this->setField('StatusDetailMessage', $message);
                }
            }
        }

        if (array_key_exists($name, $this->fields) || $force == 'data') {
            $this->fields[$name] = $value;
            return;
        }

        if (property_exists($this, $name) || $force == 'property') {
            $this->{$name} = $value;
            return;
        }

        return null;
    }

    /**
     * Get all fields for saving to the store as an array.
     */

    public function toArray()
    {
        return $this->fields;
    }

    /**
     * Save the transaction record to storage.
     * Return true if successful, false if not.
     * This may involved creating a new record, or may involve updating an existing record.
     */

    abstract public function save();

    /**
     * Find a saved transaction by its VendorTxCode.
     * Returns the TransactionAbstract object or null.
     * TODO: or should it always return $this, with an exception if not found?
     */

    abstract public function find($VendorTxCode);

    /**
     * Make a new VendorTxCode.
     * To be give the code some context, we start it with a timestamp then add
     * on a number based on milliseconds.
     * The VendorTxCode is limited to 40 characters.
     * This is 17 + 13 = 30 characters.
     */

    public function makeVendorTxCode()
    {
        $VendorTxCode = uniqid(date('Ymd-His-'), false);
        return $VendorTxCode;
    }

    /**
     * Returns true if the status of the transaction is one resulting from a
     * successful payment.
     */

    public function isPaymentSuccess()
    {
        if ($this->Status == 'OK' || $this->Status == 'AUTHENTICATED' || $this->Status == 'REGISTERED') {
            return true;
        } else {
            return false;
        }
    }
}

