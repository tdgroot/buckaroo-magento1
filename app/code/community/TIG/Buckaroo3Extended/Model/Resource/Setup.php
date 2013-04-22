<?php
class TIG_Buckaroo3Extended_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    public function getTermsAndConditions()
    {
        $termsAndConditions = <<<TERMS_AND_CONDITIONS
<p><strong>Toelichting</strong></p>
<p>Bij "achteraf betalen", kunt u eerst uw bestelling ontvangen en dan de factuur betalen.
Indien u kiest voor de betaalmethode "achteraf betalen", gaat u akkoord met de daarbij behorende
algemene voorwaarden. Voor de beoordeling van uw aanvraag worden de door u ingevulde gegevens
conform de bepalingen in de algemene voorwaarden doorgezonden naar Intrum Justitia.</p>
<p>De afhandeling van de betaling van de Klanten wordt uitbesteed aan Buckaroo. Op de factuur
staat hoe u (na ontvangst van goederen en/of diensten) de factuur dient te betalen. De
factuur dient binnen 14 (veertien) dagen na factuurdatum betaald te worden.</p>
<p>U dient dit vakje aan te klikken om aan te geven dat u akkoord gaat met de algemene voorwaarden.
Voor deze algemene voorwaarden klikt u <a href="">hier</a>.</p>
TERMS_AND_CONDITIONS;

        return $termsAndConditions;
    }
    
    public function setTermsAndConditions($termsAndConditions)
    {
        $this->_termsAndConditions = $termsAndConditions;
        return $this;
    }

    public function getInformationRequirement()
    {
        $informationRequirement = <<<INFORMATION_REQUIREMENT
<p>Voorwaarden gebruik "achteraf betalen":<br/>
1. De producten en/of diensten van <naam Merchant invullen> zijn afgenomen met een factuur-
en afleveradres (geen postbus) in Nederland;<br/>
2. Om de financiële risico's van de betaaloptie "achteraf betalen" te beperken, wordt de
bestelling via Buckaroo door Intrum Justitia getoetst. Op grond van deze toetsing wordt
bepaald of de aanvraag voor de achteraf betaling geaccepteerd wordt. Als gevolg van de
acceptatie dient u te betalen op de manier zoals nader in deze voorwaarden beschreven.
<naam Merchant invullen> wijst Intrum Justitia aan als degene aan wie moet worden betaald.<br/>
3. Indien betaaloptie "achteraf betalen" niet wordt geaccepteerd, dient u de bestelling via een
andere betaaloptie vooraf te voldoen;<br/>
4. U verklaart dat alle (aanvullende) gegevens benodigd voor de aanvraag van uw verzoek om
achteraf betaling, correct en volledig zijn opgegeven en geeft toestemming uw gegevens
te verwerken en online uw gegevens te toetsen bij Intrum Justitia, zodat u direct weet of de
aanvraag geaccepteerd wordt;<br/>
5. U bent verplicht <naam Merchant invullen> op de hoogte te stellen van iedere adres- en/
of e-mailwijziging. Zolang wij geen adreswijziging van u hebben ontvangen, wordt u geacht
woonachtig te zijn op het laatst bij ons bekende adres. Ongeacht het wel of niet doorgeven
van een adres- en/of e-mailwijziging blijft u gehouden het openstaande saldo te voldoen.
Adres- en/of e-mailwijzigingen kunnen via de website van <naam Merchant invullen> per e-
mail of schriftelijk aan de Klantenservice van <naam Merchant invullen> worden doorgegeven;<br/>
6. U verklaart dat u geen surseance van betaling heeft aangevraagd of verkeert in
schuldsanering/ bemiddeling, niet failliet bent verklaard of onder curatele gesteld en er ook
geen procedure of aanvraag loopt, welke zou kunnen resulteren in een faillissement, een
surseance van betaling, een onder curatelestelling of enig traject van schuldsanering in welke
vorm dan ook</p>
<p>De wijze van betalen<br/>
1. Intrum Justitia heeft de afhandeling van uw betaling uitbesteed aan Buckaroo B.V.
(www.buckaroo.nl). Indien uw aanvraag is geaccepteerd, ontvangt u naast de (digitale)
factuur, kort daarna ook een e-mail met betaalkoppeling van Buckaroo. Op zowel de factuur
als in de e-mail met betaalkoppeling staat aangegeven hoe u (na ontvangst van de goederen
en/of diensten) de factuur dient te betalen. Zorg ervoor dat u op tijd betaalt conform de
instructies vermeld op de factuur of via de e-mail met betaalkoppeling. U voorkomt dat
incassokosten aan u in rekening gebracht worden bij overschrijding van de betalingstermijn
van 14 (veertien) dagen.<br/>
2. Voor het ontvangen van de e-mail dient u een geldig en correct e-mailadres op te geven.
Indien u geen geldig en correct e-mailadres opgeeft, dan ontvangt u geen betaalinformatie
via de e-mail met betaalkoppeling. U bent verplicht het e-mailadres dat u opgeeft op juistheid
te controleren, alsmede de ontvangst van de e-mail met betaalkoppeling van Buckaroo te
controleren.<br/>
3. U erkent dat Buckaroo diensten aanbiedt ten behoeve van het verwerken van online
betalingen door u aan en dat Buckaroo in dat verband kennisgevingen en mededelingen
weergeeft en/of zal weergeven en handelingen verricht danwel zal verrichten namens
<naam Merchant invullen> respectievelijk Intrum Justitia, hetgeen ook als zodanig door u
als een geldige en juiste kennisgeving, mededeling en/of handeling van danwel namens
<naam Merchant invullen> respectievelijk Intrum Justitia wordt erkend.</p>
<p>Betalingsvoorwaarden en betaaltermijn:<br/>
1. Het voor uw aankopen verschuldigde bedrag dient binnen een termijn van 14 (veertien) dagen
na factuurdatum in zijn geheel, zonder enige aftrek of verrekening door Buckaroo ontvangen
te zijn.<br/>
2. Indien u niet binnen 14 (veertien) dagen na datum factuur het gehele factuurbedrag heeft
betaald, bent u zonder nadere ingebrekestelling in verzuim.<br/>
3. Bij overschrijding van de betalingstermijn heeft Intrum Justitia al dan niet namens <naam
Merchant invullen> het recht buitengerechtelijke incassokosten alsmede rente in rekening te
brengen.<br/>
4. Voor het toezenden van de betalingsherinnering en het in rekening brengen van de
incassokosten alsmede de rente bij overschrijding van de betalingstermijn wordt gebruik
gemaakt van het door u opgegeven en het door u op juistheid gecontroleerde e-mail adres.
U ontvangt hiervoor een e-mail met betaalkoppeling. Het niet (kunnen) ontvangen van
een e-mail laat onverlet dat u verantwoordelijk bent voor het op tijd betalen van het gehele
factuurbedrag alsmede de in rekening gebrachte incassokosten en rente.<br/>
5. Indien u ondanks sommatie en/of herinneringen niet het gehele bedrag (factuurbedrag
alsmede de incassokosten en rente) betaalt, dan draagt <naam Merchant invullen>
overeenkomstig deze algemene voorwaarden, 35 (vijfendertig) dagen na factuurdatum, de
gehele Vordering jegens u ook juridisch over aan Intrum Justitia.<br/>
6. U bent verplicht Intrum Justitia op de hoogte te stellen van iedere adres- en/of e-mailwijziging
gedurende de periode dat u verplicht bent het verschuldigde bedrag te betalen. Zolang Intrum
Justitia geen adreswijziging van u heeft ontvangen, wordt u geacht woonachtig te zijn op
het laatst bij ons bekende adres en blijft u gehouden om het alsdan verschuldigde bedrag te
voldoen.<br/>
7. Het doorgeven van adres- en/of e-mailwijzigingen aan Intrum Justitia kan schriftelijk. De
adresgegevens vindt u terug op de website www.intrum.nl onder Contact.<br/>
8. Indien u niet, niet volledig of niet tijdig het gehele bedrag (factuurbedrag alsmede de
incassokosten) betaalt, kan dit gevolgen hebben voor eventuele goedkeuring door Intrum
Justitia van iedere volgende aanvraag van u voor de betaaloptie "achteraf betalen".</p>
INFORMATION_REQUIREMENT;

        return $informationRequirement;
    }
    
    public function setInformationRequirement($informationRequirement)
    {
        $this->_informationRequirement = $informationRequirement;
        return $this;
    }
    
    public function installTermsAndConditions()
    {
        $currentStore = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        
        $staticBlock = Mage::getModel('cms/block');
        $intrumTermsAndConditions = $staticBlock->load('buckaroo_intrum_terms_and_conditions');
        if ($intrumTermsAndConditions->getId()) {
            return $this;
        }
        
        $parameters = array(
            'title'      => 'Buckaroo Algemene Voorwaarden',
            'identifier' => 'buckaroo_intrum_terms_and_conditions',
            'content'    => $this->getTermsAndConditions(),
            'is_active'  => 1,
            'stores'     => array(0),
        );
        
        $intrumTermsAndConditions->setData($parameters)->save();
        Mage::app()->setCurrentStore($currentStore);
        return $this;
    }
    
    public function installInformationRequirement()
    {
        $currentStore = Mage::app()->getStore()->getId();
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        
        $staticBlock = Mage::getModel('cms/block');
        $informationRequirement = $staticBlock->load('buckaroo_information_requirement');
        if ($informationRequirement->getId()) {
            return $this;
        }
        
        $parameters = array(
            'title'      => 'Buckaroo Informatieplicht',
            'identifier' => 'buckaroo_information_requirement',
            'content'    => $this->getInformationRequirement(),
            'is_active'  => 1,
            'stores'     => array(0),
        );
        
        $informationRequirement->setData($parameters)->save();
        Mage::app()->setCurrentStore($currentStore);
        return $this;
    }
}