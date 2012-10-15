<?php 
class TIG_BUckaroo3Extended_Model_Certificate extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('buckaroo3extended/certificate');
    }
}