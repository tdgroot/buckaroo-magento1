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

    protected function _neutral()
    {
        $this->_debugEmail .= "The response is neutral (not successful, not unsuccessful). \n";
        $this->_debugEmail .= "Returning response parameters\n";
        $this->sendDebugEmail();

        $responseParameters = $this->getResponse()->Services->Service->ResponseParameter;

        $parameters = array();
        foreach($responseParameters as $data) {
            $data = (array)$data;
            $parameters[$data['Name']] = $data['_'];
        }

        // TODO: return the Service values and/or save them with the quote
        Mage::register('masterpass_parameters', $parameters);
    }
}
