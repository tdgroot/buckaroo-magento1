<?php 
class TIG_Buckaroo3Extended_Model_Sources_TaxClasses
{
    public function toOptionArray()
    {
        $collection = Mage::getModel('tax/class')->getCollection()
                                                 ->distinct(true)
                                                 ->addFieldToFilter(
                                                     'class_type',
                                                     array(
                                                         'like' => 'PRODUCT'
                                                     )
                                                 )
                                                 ->load();
        
        $classes = $collection->getColumnValues('class_id');
        
        $optionArray = array();
        $optionArray[''] = array('value' => '', 'label' => Mage::helper('buckaroo3extended')->__('None'));
        foreach ($classes as $class) {
            if (empty($class)) {
                continue;
            }
            $optionArray[$class] = array('value' => $class, 'label' => Mage::getModel('tax/class')->load($class)->getClassName());
        }
       
        return $optionArray;
    }
}
