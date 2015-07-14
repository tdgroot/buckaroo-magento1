<?php
class TIG_Buckaroo3Extended_Model_Request_Quote extends TIG_Buckaroo3Extended_Model_Request_Abstract
{
    public function __construct($params)
    {
        $quote = isset($params['quote']) ? $params['quote'] : null;

        if($quote instanceof Mage_Sales_Model_Quote)
        {
            // use quote as order
            $this->setOrder($quote);
            $this->setMethod($quote->getPayment()->getMethodCode());
        }

        parent::__construct();

        // make the response use quote as order
        $this->setResponseModelClass('buckaroo3extended/response_quote');
    }
}
