<?php 
class TIG_Buckaroo3Extended_Model_Sources_Certificates
{
    public function toOptionArray()
    {
    	$collection = Mage::getModel('buckaroo3extended/certificate')->getCollection()->load();
    	
    	$collectionArray = $collection->toArray();
    	
    	$array = array();
    	foreach($collectionArray['items'] as $certificate) {
    		$array[] = array(
    			'value' => $certificate['certificate_id'],
    			'label' => $certificate['certificate_name'] . ' (' . $certificate['upload_date'] . ')',	
    		);
    	}
    	
    	return $array;
    }
}