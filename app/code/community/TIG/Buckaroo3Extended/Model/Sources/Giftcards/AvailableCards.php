<?php 
class TIG_Buckaroo3Extended_Model_Sources_Giftcards_Availablecards
{
    public function toOptionArray()
    {
        $helper = Mage::helper('buckaroo3extended');
    	$array = array(
            array(
                'value' => 'babygiftcard', 
                'label' => $helper->__('babygiftcard')
            ),
    		array(
        		'value' => 'babyparkgiftcard', 
        		'label' => $helper->__('Babypark Giftcard')
            ),
    		array(
        		'value' => 'beautywellness', 
        		'label' => $helper->__('Beauty Wellness')
            ),
    		array(
        		'value' => 'boekenbon', 
        		'label' => $helper->__('Boekenbon')
            ),
    		array(
        		'value' => 'boekenvoordeel', 
        		'label' => $helper->__('Boekenvoordeel')
            ),
    		array(
        		'value' => 'designshopsgiftcard', 
        		'label' => $helper->__('Designshops Giftcard')
            ),
    		array(
        		'value' => 'fijncadeau', 
        		'label' => $helper->__('Fijn Cadeau')
            ),
    		array(
        		'value' => 'koffiecadeau', 
        		'label' => $helper->__('Koffie Cadeau')
            ),
    		array(
        		'value' => 'kokenzo', 
        		'label' => $helper->__('Koken En Zo')
            ),
    		array(
        		'value' => 'kookcadeau', 
        		'label' => $helper->__('kook-cadeau')
            ),
    		array(
        		'value' => 'nationaleentertainmentcard', 
        		'label' => $helper->__('Nationale EntertainmentCard')
            ),
    		array(
        		'value' => 'naturesgift', 
        		'label' => $helper->__('Natures Gift')
            ),
    		array(
        		'value' => 'podiumcadeaukaart', 
        		'label' => $helper->__('PODIUM Cadeaukaart')
            ),
    		array(
        		'value' => 'shoesaccessories', 
        		'label' => $helper->__('Shoes Accessories')
            ),
    		array(
        		'value' => 'webshopgiftcard', 
        		'label' => $helper->__('Webshop Giftcard')
            ),
    		array(
        		'value' => 'wijncadeau', 
        		'label' => $helper->__('Wijn Cadeau')
            ),
    		array(
        		'value' => 'wonenzo', 
        		'label' => $helper->__('Wonen En Zo')
            ),
    		array(
        		'value' => 'yourgift', 
        		'label' => $helper->__('YourGift Card')
            ),
    		array(
        		'value' => 'fashioncheque', 
        		'label' => $helper->__('fashioncheque')
            ),
            array(
                'value' => 'sieradenhorlogescadeaukaart', 
                'label' => $helper->__('sieradenhorlogescadeaukaart')
            ),
            array(
                'value' => 'jewellerygiftcard', 
                'label' => $helper->__('JewelleryGiftcard')
            ),
    	);
    	return $array;
    }
}