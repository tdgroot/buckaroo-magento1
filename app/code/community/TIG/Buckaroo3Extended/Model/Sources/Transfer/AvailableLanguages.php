<?php
class TIG_Buckaroo3Extended_Model_Sources_Transfer_AvailableLanguages
{
    public function toOptionArray()
    {
        $array = array(
            array('label' => 'NL', 'value' => 'NL'),
            array('label' => 'EN', 'value' => 'EN'),
            array('label' => 'DE', 'value' => 'DE'),
            array('label' => 'DK', 'value' => 'DK'),
            array('label' => 'ES', 'value' => 'ES'),
            array('label' => 'FR', 'value' => 'FR'),
            array('label' => 'IT', 'value' => 'IT'),
            array('label' => 'PT', 'value' => 'PT'),
            array('label' => 'RU', 'value' => 'RU'),
            array('label' => 'SE', 'value' => 'SE'),
        );
        
    	return $array;
    }
}