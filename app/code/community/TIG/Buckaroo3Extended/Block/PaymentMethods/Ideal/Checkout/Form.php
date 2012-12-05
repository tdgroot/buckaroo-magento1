<?php
class TIG_Buckaroo3Extended_Block_PaymentMethods_Ideal_Checkout_Form extends Mage_Payment_Block_Form
{
    public function __construct()
    {
		$this->setTemplate('buckaroo3extended/ideal/checkout/form.phtml');
        parent::_construct();
    }
    
    public function getIssuerList()
    {
        $issuerArray = array(
            'ABNAMRO' => array(
                'name' => 'ABN AMRO',
                'logo' => 'logo_abn_s.gif',
            ),
            'ASNBANK' => array(
                'name' => 'ASN Bank',
                'logo' => 'icon_asn.gif',
            ),
            'FRIESLAND' => array(
                'name' => 'Friesland Bank',
                'logo' => 'logo_friesland_s.gif',
            ),
            'INGBANK' => array(
                'name' => 'ING',
                'logo' => 'logo_ing_s.gif',
            ),
            'RABOBANK' => array(
                'name' => 'Rabobank',
                'logo' => 'logo_rabo_s.gif',
            ),
            'SNSBANK' => array(
                'name' => 'SNS Bank',
                'logo' => 'logo_sns_s.gif',
            ),
            'SNSREGIO' => array(
                'name' => 'RegioBank',
                'logo' => 'logo_sns_s.gif',
            ),
            'TRIODOS' => array(
                'name' => 'Triodos Bank',
                'logo' => 'logo_triodos.gif',
            ),
            'LANSCHOT' => array(
                'name' => 'Van Lanschot',
                'logo' => 'logo_lanschot_s.gif',
            ),
            'KNAB' => array(
                'name' => 'Knab',
                'logo' => 'logo_knab_s.gif',
            ),
        );
        
        return $issuerArray;
    }
}