<?php
class TIG_Buckaroo3Extended_Model_PaymentMethods_Afterpay_PaymentMethod extends TIG_Buckaroo3Extended_Model_PaymentMethods_PaymentMethod
{
    public $allowedCurrencies = array(
        'EUR',
    );

    protected $_code = 'buckaroo3extended_afterpay';

    protected $_formBlockType = 'buckaroo3extended/paymentMethods_afterpay_checkout_form';

    protected $_canRefund               = false;
    protected $_canRefundInvoicePartial = false;

    public function getOrderPlaceRedirectUrl()
    {
        $session = Mage::getSingleton('checkout/session');

        $post = Mage::app()->getRequest()->getPost();

        $accountNumber = $post[$this->_code.'_bpe_customer_account_number'];

        $customerBirthDate = date(
            'Y-m-d', strtotime($post['payment'][$this->_code]['year']
                . '-' . $post['payment'][$this->_code]['month']
                . '-' . $post['payment'][$this->_code]['day'])
        );

        $array = array(
            'BPE_Customergender'    => $post[$this->_code.'_BPE_Customergender'],
            'BPE_AccountNumber'     => $this->filterAccount($accountNumber),
            'BPE_PhoneNumber'       => $post[$this->_code.'_bpe_customer_phone_number'],
            'BPE_customerbirthdate' => $customerBirthDate,
            'BPE_B2B'               => (int)$post['buckaroo3extended_afterpay_BPE_BusinessSelect'],
        );

        if((int)$array['BPE_B2B'] == 2){
            $additionalArray = array(
                'BPE_CompanyCOCRegistration' => $post['buckaroo3extended_afterpay_BPE_CompanyCOCRegistration'],
                'BPE_CompanyName'            => $post['buckaroo3extended_afterpay_BPE_CompanyName'],
                'BPE_CostCentre'             => $post['buckaroo3extended_afterpay_BPE_CostCentre'],
                'BPE_VatNumber'              => $post['buckaroo3extended_afterpay_BPE_VatNumber'],
            );

            $array = array_merge($array,$additionalArray);
        }

        $session->setData('additionalFields',$array);

        return parent::getOrderPlaceRedirectUrl();
    }

    /**
     * @param null | Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        return false;

        if(is_null($quote) && Mage::helper('buckaroo3extended')->isAdmin()){
            // Uncomment this code to get all active Buckaroo payment methods in the backend. (3th party extensions)
            /*if(Mage::getStoreConfigFlag('buckaroo/' . $this->_code . '/active', Mage::app()->getStore()->getId())){
                return true;
            }*/

            return false;
        }

        $quoteItems = $quote->getAllVisibleItems();
        if(count($quoteItems) > 9){
            return false;
        }

        $session = Mage::getSingleton('checkout/session');
        if($session->getData('buckarooAfterpayRejected') == true){
            return false;
        }


        //check if the country specified in the billing address is allowed to use this payment method
        if (Mage::getStoreConfig('buckaroo/' . $this->_code . '/allowspecific', $quote->getStoreId()) == 1
            && $quote->getBillingAddress()->getCountry())
        {
            $allowedCountries = explode(',',Mage::getStoreConfig('buckaroo/' . $this->_code . '/specificcountry', $quote->getStoreId()));
            $country = $quote->getBillingAddress()->getCountry();

            if (!in_array($country,$allowedCountries)) {
                return false;
            }
        }

        $areaAllowed = null;
        if ($this->canUseInternal()) {
            $areaAllowed = Mage::getStoreConfig('buckaroo/' . $this->_code . '/area', $quote->getStoreId());
        }

        //check if the paymentmethod is available in the current shop area (frontend or backend)
        if ($areaAllowed == 'backend'
            && !Mage::helper('buckaroo3extended')->isAdmin()
        ) {
            return false;
        } elseif ($areaAllowed == 'frontend'
            && Mage::helper('buckaroo3extended')->isAdmin()
        ) {
            return false;
        }

        // check if max amount for the issued PaymentMethod is set and if the quote basegrandtotal exceeds that
        $maxAmount = Mage::getStoreConfig('buckaroo/' . $this->_code . '/max_amount', $quote->getStoreId());
        if (!empty($maxAmount)
            && !empty($quote)
            && $quote->getBaseGrandTotal() > $maxAmount)
        {
            return false;
        }

        // check if min amount for the issued PaymentMethod is set and if the quote basegrandtotal is less than that
        $minAmount = Mage::getStoreConfig('buckaroo/' . $this->_code . '/min_amount', $quote->getStoreId());
        if (!empty($minAmount)
            && !empty($quote)
            && $quote->getBaseGrandTotal() < $minAmount)
        {
            return false;
        }

        //check if the module is set to enabled
        if (!Mage::getStoreConfig('buckaroo/' . $this->_code . '/active', $quote->getStoreId())) {
            return false;
        }

        //limit by ip
        if (mage::getStoreConfig('dev/restrict/allow_ips') && Mage::getStoreConfig('buckaroo/' . $this->_code . '/limit_by_ip'))
        {
            $allowedIp = explode(',', mage::getStoreConfig('dev/restrict/allow_ips'));
            if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIp))
            {
                return false;
            }
        }

        // get current currency code
        $currency = Mage::app()->getStore()->getBaseCurrencyCode();


        // currency is not available for this module
        if (!in_array($currency, $this->allowedCurrencies))
        {
            return false;
        }

        return TIG_Buckaroo3Extended_Model_Request_Availability::canUseBuckaroo($quote);
    }
}
