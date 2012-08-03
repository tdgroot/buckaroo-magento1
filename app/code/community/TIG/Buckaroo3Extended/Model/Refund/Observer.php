<?php
class TIG_Buckaroo3Extended_Model_Refund_Observer extends Mage_Core_Model_Abstract 
{    
    public function sales_order_payment_refund(Varien_Event_Observer $observer) 
    {
        $payment = $observer->getPayment();
        $creditmemo = $observer->getCreditmemo();
        
        $additionalInformation = $payment->getAdditionalInformation();
        
        $transactionKey = $additionalInformation['buckaroo_transaction_key'];
        $refundState = $additionalInformation['buckaroo_refund_state'];
        
        switch ($refundState) {
            case 'success': $creditmemo->setState('2');
                            break;
            case 'failure':
            case 'error':   $creditmemo->setState('3');
                            break;
            case 'pending': $creditmemo->setState('1');
                            break;
        }
        
        $creditmemo->setTransactionKey($transactionKey);
        $creditmemo->save();
        
        return $this;
    }
}