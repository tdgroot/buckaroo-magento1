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
}
