<?php 
class TIG_Buckaroo3Extended_Model_Refund_Creditmemo extends TIG_Buckaroo3Extended_Model_Refund_Response_Push
{
	/**
	 * This is called when a refund is made in Buckaroo Payment Plaza.
	 * This Function will result in a creditmemo being created for the order in question.
	 */
	public function processBuckarooRefundPush()
	{
		//check if the push is valid and if the order can be updated
		$canProcessPush = $this->_canProcessRefundPush();
		list($canProcess, $canUpdate) = $canProcessPush;
		
		$this->_debugEmail .= "Is the PUSH valid? " . $canProcess . "\nCan the creditmemo be created? " . $canUpdate . "\n";
		
		if (!$canProcess || !$canUpdate) {
			return false;
		}
	    
		$this->_createCreditmemo();
	}
	
	protected function _createCreditmemo()
	{
		$data = $this->_getCreditmemoData();
		
        try {
            $creditmemo = $this->_initCreditmemo($data);
            
            if ($creditmemo) {
                if (($creditmemo->getGrandTotal() <=0) && (!$creditmemo->getAllowZeroGrandTotal())) {
                    Mage::throwException(
                        $this->__('Credit memo\'s total must be positive.')
                    );
                }

                $comment = '';
                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
                    if (isset($data['comment_customer_notify'])) {
                        $comment = $data['comment_text'];
                    }
                }

                if (isset($data['do_refund'])) {
                    $creditmemo->setRefundRequested(true);
                }
                if (isset($data['do_offline'])) {
                    $creditmemo->setOfflineRequested((bool)(int)$data['do_offline']);
                }

                $creditmemo->register();
                if (!empty($data['send_email'])) {
                    $creditmemo->setEmailSent(true);
                }

                $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $this->_saveCreditmemo($creditmemo);
                $creditmemo->sendEmail(!empty($data['send_email']), $comment);
                
                Mage::getSingleton('adminhtml/session')->getCommentText(true);
            }
        } catch (Mage_Core_Exception $e) {
            Mage::log($e->getMessage(), null, 'TIG_R4.log', true);
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'TIG_R4.log', true);
        }
	}
	
    protected function _initCreditmemo($data, $update = false)
    {
        $creditmemo = false;
        
        $order  = $this->_order;
        
        $service = Mage::getModel('sales/service_order', $order);
        
        $savedData = $this->_getItemData($data);
        
        $qtys = array();
        foreach ($savedData as $orderItemId =>$itemData) {
            if (isset($itemData['qty'])) {
                $qtys[$orderItemId] = $itemData['qty'];
            }
        }
        $data['qtys'] = $qtys;
        $creditmemo = $service->prepareCreditmemo($data);
        
        $args = array('creditmemo' => $creditmemo, 'request' => $data);
        Mage::dispatchEvent('adminhtml_sales_order_creditmemo_register_before', $args);

        Mage::register('current_creditmemo', $creditmemo);
        return $creditmemo;
    }

    /**
     * Save creditmemo and related order, invoice in one transaction
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    protected function _saveCreditmemo($creditmemo)
    {
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($creditmemo)
            ->addObject($creditmemo->getOrder());
        if ($creditmemo->getInvoice()) {
            $transactionSave->addObject($creditmemo->getInvoice());
        }
        $transactionSave->save();

        return $this;
    }
    
    /**
     * Get requested items qtys and return to stock flags
     */
    protected function _getItemData($data = false)
    {
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }
        return $qtys;
    }
    
    /**
     * Most of the code used to create a creditmemo is copied and modified from the default magento code.
     * However, that code expects an array with values. This method creates that array.
     * 
     * @return array $data
     */
    protected function _getCreditmemoData()
    {
        $data = array(
            'do_offline' => '1',
            'comment_text' => '',
            'shipping_amount' => '0',
            'adjustment_positive' => $this->_postArray['brq_amount_credit'],
            'adjustment_negative' => '0',
        );
        
        $items = array();
        foreach ($this->_order->getAllItems() as $orderItem)
        {
            if (!in_array(array_flip($items))) {
                $items[$orderItem->getId()] = array(
                    'qty' => 0,
                );
            }
        }
        
        $data['items'] = $items;
        return $data;
    }
    
	/**
     * Checks if the post recieved is valid by checking its signature field.
     * This field is unique for every payment and every store.
     * Also calls a method that checks if the order is able to have a creditmemo
     * 
     * @return array $return
     */
	protected function _canProcessRefundPush()
	{
	    $correctSignature = false;
		$canUpdate = false;
	    $signature = $this->_calculateSignature();
	    if ($signature === $this->_postArray['brq_signature']) {
	        $correctSignature = true;
	    }
	    
		//check if the order can recieve a new creditmemo
		if ($correctSignature === true) {
			$canUpdate = $this->_order->canCreditmemo();
		}
		
		$return = array(
			(bool) $correctSignature,
			(bool) $canUpdate,
		);
		return $return;
	}
}