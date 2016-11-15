<?php
class Creativestyle_MagmiExport_Adminhtml_MagmiexportbackendController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Magmi Products Export"));
	   $this->renderLayout();
    }

    public function postAction() {

    	if(isset($_POST['magmiexport'])){
            $params = $this->getPostData();
            $this->getCollection($params);
    	} else {
        Mage::getSingleton('adminhtml/session')->addError('Wrong post action');
        }

  	    $this->_redirect('*/*/');
        return;

    }

    private function getPostData() {
        $params = $this->getRequest()->getParams();
        return $params;
    }

    private function getCollection($params) {

        $options = $params['options'];
        $fields = $params['fields'];

        $range = $this->getRange($options['export_range']);

        $collection = $this->getProducts($range,$fields);

        $this->createCSV($collection,$fields);

    }

    private function createCSV($products,$fields) {
        $date = date('Y-m-d');
    	$fileName = 'magmiProductExport_'.$date.'.csv';
    	$file_path = Mage::getBaseDir('media').'/'.$fileName;
    	$mage_csv = new Varien_File_Csv();
    	$mage_csv->setDelimiter(',');
    	$mage_csv->setEnclosure('"');

        $data = $this->getCsvData($products,$fields);

        $mage_csv->saveData($file_path, $data);

    	$link = $this->getDownloadLink($file_path);

        Mage::getSingleton('adminhtml/session')->addSuccess($link);
    }

    protected function getCsvData($products,$fields) {

       $header = array_keys($fields);
       $data[] = $header;

        foreach($products as $_product) {
            $smalldata = array();
                foreach($fields as $key => $val) {
                    $smalldata[$key] = $_product[$key];
                }
            $data[] = $smalldata;
        }

        return $data;

    }

    protected function getDownloadLink($file_path) {
    	$link = '<a href="'.$file_path.'"> Download link </a>';
    	return $link;
    }

    private function getRange($range) {
        switch($range) {
            case '1':
                $val = 'simple';
                break;
            case '2':
                $val = 'configurable';
                break;
            case '3':
                $val = '*';
                break;
        }

        return $val;
    }

    private function getProducts($range,$attributes) {

        $filter = $this->getAttributes($attributes);

        if($filter == '*') {
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect($filter);
        } else {
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect($filter)
            ->addAttributeToFilter('type_id', $range);
        }

        return $collection;

    }

    private function getAttributes($data) {
        $filter = array();
        foreach ($data as $key => $value) {
            $filter[] = array(
                'attribute' => $key,
            );
        }

        return $filter;
    }

}
