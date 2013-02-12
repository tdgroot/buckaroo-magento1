<?php
class TIG_Buckaroo3Extended_Block_PaymentMethods_Checkout_Form_Abstract extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $session = Mage::getSingleton('checkout/session');
        $this->setSession($session);
        $this->setCustomer(Mage::getSingleton('customer/session')->getCustomer());
        $this->setAddress($session->getQuote()->getBillingAddress());
        $this->setQuote($session->getQuote());
        
        return parent::_construct();
    }
    
    public function getMethodLabelAfterHtml($useSpan = true)
    {
        $code = $this->getMethod()->getCode();
		
        $feeAllowed = Mage::getStoreConfig('buckaroo/'. $this->_method . '/active_fee', Mage::app()->getStore()->getId());
		if (!$feeAllowed) {
			return '';
		}
		
        $fee = Mage::getStoreConfig('buckaroo/' . $code . '/payment_fee', mage::app()->getStore()->getId());
        
        if (!$fee) {
            return '';
        }
        
        $fee = str_replace(',', '.', $fee);
        
        if (strpos($fee, '%') === false) {
            $fee = Mage::helper('core')->currency($fee, true, false);
        }
        
        $feeText = '';
        if ($useSpan) {
            $feeText .= '<span class="buckaroo_fee '
                      . $code
                      . '">';
        }
        
        $feeText .= Mage::helper('buckaroo3extended')->__('%s fee', $fee);
                 
        if ($useSpan) {
            $feeText .= '</span>';
        }

        return $feeText;
    }

    public function getName()
    {
        $name = $this->getSession()->getData($this->getMethodCode() . '_BPE_Customername');
        if (!$name) {
            $address = $this->getAddress();
            $firstname = $this->getFirstname();
            $lastname = $this->getLastname();
            
            $name = $firstname . ' ' . $lastname;
        }
        
        return $name;
    }
    
    public function getFirstname()
    {
        $firstname = $this->getSession()->getData($this->getMethodCode() . '_BPE_Customerfirstname');
        
        if (!$firstname) {
            $firstname = $this->getAddress()->getFirstname();
        }
        
        if (!$firstname && $this->getCustomer()) {
            $firstname = $this->getCustomer()->getFirstname();
        }
        
        return $firstname;
    }
    
    public function getLastname()
    {
        $lastname = $this->getSession()->getData($this->getMethodCode() . '_BPE_Customerlastname');
        
        if (!$lastname) {
            $lastname = $this->getAddress()->getLastname();
        }
        
        if (!$lastname && $this->getCustomer()) {
            $lastname = $this->getCustomer()->getLastname();
        }
        
        return $lastname;
    }
    
    public function getGender()
    {
        $gender = (int) $this->getSession()->getData($this->getMethodCode() . '_BPE_Customergender');
        if (!$gender) {
            $gender = (int) $this->getAddress()->getCustomerGender();
        }

        return $gender;
    }
    
    public function getDob()
    {
        if ($this->getSession()->getData($this->getMethodCode() . '_customerbirthdate[day]')) {
            $dob = date('d,m,Y', strtotime($this->getQuote()->getCustomerDob()));
            $dob = explode(',', $dob);
        } elseif ($this->getAddress()->getCustomerDob()) {
            $dob = array(
                $this->getSession()->getData($this->getMethodCode() . '_customerbirthdate[day]'),
                $this->getSession()->getData($this->getMethodCode() . '_customerbirthdate[month]'),
                $this->getSession()->getData($this->getMethodCode() . '_customerbirthdate[year]'),
            );
        } else {
            $dob = array(
                false,
                false,
                false,
            );
        }
        
        return $dob;
    }
    
    public function getEmail()
    {
        $email = $this->getSession()->getData($this->getMethodCode() . '_BPE_Customeremail');
        
        if (!$email) {
            $this->getAddress()->getEmail();
        }
        
        if (!$email && $this->getCustomer()) {
            $email = $this->getCustomer()->getEmail();
        }
        
        return $email;
    }
    
    public function getBankAccount()
    {
        $account = $this->getSession()->getData($this->getMethodCode() . '_bpe_customer_account_number');
        
        return $account;
    }
}
