<?php
class TIG_Buckaroo3Extended_Model_Response_Quote extends TIG_Buckaroo3Extended_Model_Response_Abstract
{
    public function __construct($data)
    {
        // get quote from session
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();

        // use quote as order
        $this->setOrder($quote);
        $this->setMethod($quote->getPayment()->getMethodCode());

        parent::__construct($data);
    }

    protected function _success()
    {
        $this->sendDebugEmail();

        // this will never happen, since we are working with a quote
        Mage::throwException('An error occurred while processing the request');
    }

    protected function _failed()
    {
        $this->_debugEmail .= 'The transaction was unsuccessful. \n';
        $this->_debugEmail .= "Returning response parameters.\n";
        $this->sendDebugEmail();

        return array(
            'error' => Mage::helper('buckaroo3extended')->__('Your payment was unsuccesful. Please try again or choose another payment method.'),
        );
    }

    protected function _error()
    {
        $this->_debugEmail .= "The transaction generated an error. \n";
        $this->_debugEmail .= "Returning response parameters.\n";
        $this->sendDebugEmail();

        return array(
            'error' => Mage::helper('buckaroo3extended')->__('A technical error has occurred. Please try again. If this problem persists, please contact the shop owner.'),
        );
    }

    protected function _rejected()
    {
        $this->_debugEmail .= "The transaction generated an error. \n";
        $this->_debugEmail .= "Returning response parameters.\n";
        $this->sendDebugEmail();

        return array(
            'error' => Mage::helper('buckaroo3extended')->__('The payment has been rejected, please try again or select a different paymentmethod.'),
        );
    }

    protected function _neutral()
    {
        $this->_debugEmail .= "The response is neutral (not successful, not unsuccessful). \n";
        $this->_debugEmail .= "Returning response parameters.\n";
        $this->sendDebugEmail();

        $parameters = array();
        $responseParameters = $this->getResponse();

        if(isset($responseParameters->Services->Service->ResponseParameter))
        {
            foreach($responseParameters->Services->Service->ResponseParameter as $responseParameter)
            {
                $parameters[lcfirst($responseParameter->Name)] = $responseParameter->_;
            }
        }

        return $parameters;
    }

    protected function _verifyError()
    {
        $this->_debugEmail .= "The transaction's authenticity was not verified. \n";
        $this->_debugEmail .= "Returning response parameters.\n";
        $this->sendDebugEmail();

        return array(
            'error' => Mage::helper('buckaroo3extended')->__('We are currently unable to retrieve the status of your transaction. If you do not recieve an e-mail regarding your order within 30 minutes, please contact the shop owner.'),
        );
    }
}
