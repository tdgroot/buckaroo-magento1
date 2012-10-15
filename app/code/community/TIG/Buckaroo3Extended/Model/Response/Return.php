<?php 
class TIG_Buckaroo3Extended_Model_Response_Return extends TIG_Buckaroo3Extended_Model_Response_Push
{
    public function processReturn()
    {
        //check if the push is valid and if the order can be updated
		list($canProcess, $canUpdate) = $this->_canProcessPush(true);
		
		$this->_debugEmail .= "can the order be processed? " . $canProcess . "\ncan the order be updated? " . $canUpdate . "\n";
		
		if (!$canProcess) {
			$this->_verifyError();
		}
		
		$parsedResponse = $this->_parsePostResponse($this->_postArray['brq_statuscode']);
		
		$this->_requiredAction($parsedResponse);
    }
}