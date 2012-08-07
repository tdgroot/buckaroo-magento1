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
                if (($creditmemo->getGrandTotal() <= 0) && (!$creditmemo->getAllowZeroGrandTotal())) {
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
    
        /**
         * Process back to stock flags
         */
        foreach ($creditmemo->getAllItems() as $creditmemoItem) {
            $orderItem = $creditmemoItem->getOrderItem();
            $parentId = $orderItem->getParentItemId();
            
            $creditmemoItem->setBackToStock(false);
        }
        
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
            'do_offline'      => '0',
            'do_refund'       => '0',
            'comment_text'    => '',
        );
        
        $totalToRefund = $this->_postArray['brq_amount_credit'] + $this->_order->getBaseTotalRefunded();
        if ($totalToRefund == $this->_order->getBaseGrandTotal()) {
            //if the amount to be refunded + the amount that has already been refunded equals the order's base grandtotal
            //all products from that order will be refunded as well
            $data['shipping_amount']     = $this->_order->getBaseShippingAmount() - $this->_order->getBaseShippingRefunded();
            $data['adjustment_negative'] = $this->_order->getBaseTotalRefunded() - $this->_order->getBaseShippingRefunded();

            $remainder = $this->_calculateRemainder();
            
            $data['adjustment_positive'] = $remainder;
        } else {
            //if the above is not the case; no products will be refunded and this refund will be considered an
            //adjustment refund
            $data['shipping_amount']     = '0';
            $data['adjustment_negative'] = '0';
            $data['adjustment_positive'] = $this->_postArray['brq_amount_credit'];
        }
        
        $items = $this->_getCreditmemoDataItems();
        
        $data['items'] = $items;
        return $data;
    }
    
    /**
     * Calculates the amount left over after discounts, shipping, taxes, adjustments and the subtotal have been
     * taken into account. This remainder is probably caused by some module such as a paymentfee. 
     * 
     * This method will return 0 in most cases.
     */
    protected function _calculateRemainder()
    {
        $baseTotalToBeRefunded = (
                                   $this->_order->getBaseShippingAmount() 
                                   - $this->_order->getBaseShippingRefunded()
                               ) + (
                                   $this->_order->getBaseSubtotal()
                                   - $this->_order->getBaseSubtotalRefunded()
                               ) + (
                                   $this->_order->getBaseAdjustmentNegative()
                                   - $this->_order->getBaseAdjustmentPositive()
                               );
                               
        $remainderToBeRefunded = $this->_order->getBaseGrandTotal() 
                               - $baseTotalToBeRefunded
                               - $this->_order->getBaseTotalRefunded();
                               
        return $remainderToBeRefunded;
    }
    
    /**
     * Determines which items need to be refunded. If the amount to be refunded equals the order base grandtotal
     * thern all items are refunded, otherwise none are
     */
    protected function _getCreditmemoDataItems()
    {
        $items = array();
        foreach ($this->_order->getAllItems() as $orderItem)
        {
            if (!in_array($orderItem->getId(), array_flip($items))) {
               if (($this->_postArray['brq_amount_credit'] + $this->_order->getBaseTotalRefunded()) == $this->_order->getBaseGrandTotal()) {
                    $qty = $orderItem->getQtyInvoiced();
                } else {
                    $qty = 0;
                }
                $items[$orderItem->getId()] = array(
                	'qty' => $qty,
                );
            }
        }
        
        return $items;
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