<?php 
class TIG_Buckaroo3Extended_Model_Sources_Giftcards_Availablecards
{
    public function toOptionArray()
    {
    	$array = array(
    		 array('value' => 'babygiftcard', 'label' => Mage::helper('buckaroo3extended')->__('babygiftcard')),
    		 array('value' => 'babyparkgiftcard', 'label' => Mage::helper('buckaroo3extended')->__('Babypark Giftcard')),
    		 array('value' => 'beautywellness', 'label' => Mage::helper('buckaroo3extended')->__('Beauty Wellness')),
    		 array('value' => 'boekenbon', 'label' => Mage::helper('buckaroo3extended')->__('Boekenbon')),
    		 array('value' => 'boekenvoordeel', 'label' => Mage::helper('buckaroo3extended')->__('Boekenvoordeel')),
    		 array('value' => 'designshopsgiftcard', 'label' => Mage::helper('buckaroo3extended')->__('Designshops Giftcard')),
    		 array('value' => 'fijncadeau', 'label' => Mage::helper('buckaroo3extended')->__('Fijn Cadeau')),
    		 array('value' => 'koffiecadeau', 'label' => Mage::helper('buckaroo3extended')->__('Koffie Cadeau')),
    		 array('value' => 'kokenzo', 'label' => Mage::helper('buckaroo3extended')->__('Koken En Zo')),
    		 array('value' => 'kookcadeau', 'label' => Mage::helper('buckaroo3extended')->__('kook-cadeau')),
    		 array('value' => 'nationaleentertainmentcard', 'label' => Mage::helper('buckaroo3extended')->__('Nationale EntertainmentCard')),
    		 array('value' => 'naturesgift', 'label' => Mage::helper('buckaroo3extended')->__('Natures Gift')),
    		 array('value' => 'podiumcadeaukaart', 'label' => Mage::helper('buckaroo3extended')->__('PODIUM Cadeaukaart')),
    		 array('value' => 'shoesaccessories', 'label' => Mage::helper('buckaroo3extended')->__('Shoes Accessories')),
    		 array('value' => 'webshopgiftcard', 'label' => Mage::helper('buckaroo3extended')->__('Webshop Giftcard')),
    		 array('value' => 'wijncadeau', 'label' => Mage::helper('buckaroo3extended')->__('Wijn Cadeau')),
    		 array('value' => 'wonenzo', 'label' => Mage::helper('buckaroo3extended')->__('Wonen En Zo')),
    		 array('value' => 'yourgift', 'label' => Mage::helper('buckaroo3extended')->__('YourGift Card')),
    		 array('value' => 'fashioncheque', 'label' => Mage::helper('buckaroo3extended')->__('fashioncheque')),
    	);
    	return $array;
    }
}