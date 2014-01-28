<?php

class ServicesController extends Shineisp_Controller_Default {
	protected $customer;
	protected $services;
	protected $translator;
	
	/**
	 * preDispatch
	 * Starting of the module
	 * (non-PHPdoc)
	 * @see library/Zend/Controller/Shineisp_Controller_Default#preDispatch()
	 */
	
	public function preDispatch() {
		$NS = new Zend_Session_Namespace ( 'Default' );
		
		if (empty($NS->customer)) {
			$this->_helper->redirector ( 'index', 'index', 'default' );
		}
		
		$this->customer = $NS->customer;
		$this->services = new OrdersItems ( );
		$this->translator = Shineisp_Registry::getInstance ()->Zend_Translate;
		
		// Set the navigation menu for the client control panel page on the left sidebar
		#$this->view->placeholder ( "left" )->append ( $string);	
		
		$this->getHelper ( 'layout' )->setLayout ( '1column' );
	}
	
	/**
	 * indexAction
	 * Redirect the user to the list action
	 * @return unknown_type
	 */
	public function indexAction() {
		$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'redirector' );
		$redirector->gotoUrl ( '/default/services/list' );
	}
	
	public function listAction() {
		$NS = new Zend_Session_Namespace ( 'Default' );
		$arrSort = array ();
		$params = array ();
		
		$page = $this->getRequest ()->getParam ( 'page' );
		$sort = $this->getRequest ()->getParam ( 'sort' );
		
		if (! empty ( $sort )) {
			$arrSort [] = $this->SortingData ( $sort );
			$arrSort [] = $sort;
		}
		
		$page = ! empty ( $page ) && is_numeric ( $page ) ? $page : 1;
		$params['search'][] = array ('method' => 'andWhere', 'criteria' => "(c.customer_id = ? OR c.parent_id = ?)", 'value' => array($NS->customer ['customer_id'], $NS->customer ['customer_id']));
// 		$params['search'][] = array ('method' => 'whereIn', 'criteria' => "o.status_id", 'value' => array(Statuses::id('paid', 'orders'), Statuses::id('complete', 'orders')));
		$data = $this->services->findAll ( "d.order_id, oid.relationship_id, d.description, CONCAT(dm.domain, '.', ws.tld) as domain, s.status as Status, DATE_FORMAT(d.date_start, '".settings::getMySQLDateFormat()."') as Creation_Date, DATEDIFF(d.date_end, CURRENT_DATE) as daysleft, DATE_FORMAT(d.date_end, '".settings::getMySQLDateFormat()."') as Expiring_Date, d.product_id", $page, $NS->recordsperpage, $arrSort, $params );
		
		$data ['currentpage'] = $page;
		
		for ($i=0; $i<count($data['records']); $i++){
		   $data['records'][$i]['description'] = Shineisp_Commons_Utilities::truncate($data['records'][$i]['description'], 40);
		   $data['records'][$i]['daysleft'] = ($data['records'][$i]['daysleft'] < 30) ? "<span class='label label-danger'>".$data['records'][$i]['daysleft']."</span>" : "<span class='label label-success'>".$data['records'][$i]['daysleft']."</span>";
		}
		
		
		$data ['columns'][] = $this->translator->translate('Description');
		$data ['columns'][] = $this->translator->translate('Domain');
		$data ['columns'][] = $this->translator->translate('Status');
		$data ['columns'][] = $this->translator->translate('Creation Date');
		$data ['columns'][] = $this->translator->translate('Days left');
		$data ['columns'][] = $this->translator->translate('Expiry Date');
		
		$this->view->mex = $this->getRequest ()->getParam ( 'mex' );
		$this->view->mexstatus = $this->getRequest ()->getParam ( 'status' );
		$this->view->title = $this->translator->translate("Services List");
		$this->view->description = $this->translator->translate("List of all your own services subscribed");
		$this->view->service = $data;
	}
	
	/**
	 * editAction
	 * Get a record and populate the application form 
	 * @return unknown_type
	 */
	public function editAction() {
		$currency = Shineisp_Registry::getInstance ()->Zend_Currency;
		$id = $this->getRequest ()->getParam ( 'id' );
		
		if (! empty ( $id ) && is_numeric ( $id )) {
			$NS = new Zend_Session_Namespace ( 'Default' );
			$NS->productid	= $id;
			
			$form = $this->getForm ( '/services/process' );
			//Add upgrade service if exists
			
			$fields = "o.order_id as order, pd.name as product, CONCAT(d.domain, '.', ws.tld) as domain, oi.status_id, oi.detail_id, DATE_FORMAT(o.order_date, '%d/%m/%Y') as order_date, DATE_FORMAT(oi.date_end, '%d/%m/%Y') as next_deadline, (DATEDIFF(oi.date_end, CURRENT_DATE)) as daysleft, b.name, oi.price as price, t.name as tax, t.percentage as vat, s.status as status, bc.name as billing_cycle, oi.autorenew as autorenew, oi.note as note";
			$rs = $this->services->getAllInfo ( $id, $fields, 'c.customer_id = ' . $NS->customer ['customer_id'] . ' OR c.parent_id = ' . $NS->customer ['customer_id'] );
			
			if (empty ( $rs )) 
				$this->_helper->redirector ( 'list', 'services', 'default', array ('mex' => 'The service selected has been not found.', 'status' => 'danger' ) );
			
			if (! empty ( $rs['vat'] ) && $rs ['price'] > 0) {
				$rs['total_with_tax'] = $currency->toCurrency($rs['price'] * (100 + $rs['vat']) / 100, array('currency' => Settings::findbyParam('currency')));
				$rs['tax'] = $rs ['vat'] . "% " . $this->translator->translate ( $rs['tax'] );
			}
			
			$form->populate ( $rs  );
			
			// Hide these fields and values inside the vertical grid object
			unset ( $rs['autorenew'] );
			unset ( $rs['vat'] );
			unset ( $rs['note'] );
			
			if ($rs['status_id'] == Statuses::id("complete", "orders")) {
				$this->view->expired = true;
			} else {
				$this->view->expired = false;
			}
				
			$this->view->datagrid = array ('records' => array($rs) );
			$this->view->id = $id;
			$this->view->setup = OrdersItems::getSetupConfig($id);
			
			// Get all the messages attached to the ordersitems
			$this->view->messages = Messages::getbyServiceId ($id);
			
			$this->view->title = $rs ['product'];
		}else{
		    $this->view->title = $this->translator->translate("Detail of the service");
		}
		
		
		$this->view->description = $this->translator->translate("Here you can see the detail of the service.");
		$this->view->dnsdatagrid = $this->dnsGrid ();
		$this->view->form = $form;
		$this->_helper->viewRenderer ( 'customform' );
	}
	
	/**
	 * processAction
	 * Update the record previously selected
	 * @return unknown_type
	 */
	public function processAction() {
		$request = $this->getRequest ();
		
		// Check if we have a POST request
		if (! $request->isPost ()) {
			return $this->_helper->redirector ( 'index' );
		}
		
		// Get our form and validate it
		$form = $this->getForm ( '/admin/service/process' );
		if (! $form->isValid ( $request->getPost () )) {
			// Invalid entries
			$this->view->form = $form;
			$this->view->title = $this->translator->translate("Service");
			$this->view->description = $this->translator->translate("Check all the fields and click on the save button");
			return $this->_helper->viewRenderer ( 'customform' ); // re-render the login form
		}
		
		// Get the values posted
		$params = $form->getValues ();
		
		// Get the id 
		$id = $this->getRequest ()->getParam ( 'detail_id' );
		
		if (is_numeric ( $id )) {
			OrdersItems::setAutorenew($id, $params ['autorenew']);
		}
		
		// Save the message note
		if (! empty ( $params ['message'] )) {
			
			Messages::addMessage($params ['message'], $this->customer ['customer_id'], null, null, $id);
			$isp = Shineisp_Registry::get('ISP');
			
			$placeholder['fullname'] = $this->customer ['firstname'] . " " . $this->customer ['lastname'];
			$placeholder['messagetype'] = $this->translator->translate('Order Details');
			$placeholder['message'] = $params ['message'];
		
			Messages::sendMessage ( "message_new", $this->customer ['email'], $placeholder);
			Messages::sendMessage ( "message_admin", $isp->email, $placeholder);
			
		}
		 
		$this->_helper->redirector ( 'edit', 'services', 'default', array ('id'=>$id, 'mex' => 'The task requested has been executed successfully.', 'status' => 'success' ) );
	}
	
	/**
	 * getForm
	 * Get the customized application form 
	 * @return unknown_type
	 */
	private function getForm($action) {
		$form = new Default_Form_ServicesForm ( array ('action' => $action, 'method' => 'post' ) );
		return $form;
	}
	
	/**
	 * SortingData
	 * Manage the request of sorting of the user 
	 * @return string
	 */
	private function sortingData($sort) {
		$strSort = "";
		if (! empty ( $sort )) {
			$sort = addslashes ( htmlspecialchars ( $sort ) );
			$sorts = explode ( "-", $sort );
			
			foreach ( $sorts as $sort ) {
				$sort = explode ( ",", $sort );
				$strSort .= $sort [0] . " " . $sort [1] . ",";
			}
			
			if (! empty ( $strSort )) {
				$strSort = substr ( $strSort, 0, - 1 );
			}
		}
		
		return $strSort;
	}
	
	/**
	 * recordsperpage
	 * Set the number of the records per page
	 * @return unknown_type
	 */
	public function recordsperpageAction() {
		$NS = new Zend_Session_Namespace ( 'Default' );
		$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'redirector' );
		$records = $this->getRequest ()->getParam ( 'id' );
		if (! empty ( $records ) && is_numeric ( $records )) {
			$NS->recordsperpage = $records;
		} elseif (! empty ( $records ) && $records == "all") {
			$NS->recordsperpage = 999999;
		}
		$redirector->gotoUrl ( '/services/' );
	}
	
	/**
	 * bulkexport
	 * Custom function called by the Bulk action method
	 * @param $items
	 * @return url link
	 */
	public function bulkExport($items) {
		if (is_array ( $items )) {
			$fields = "d.domain, d.tld";
			$service = $this->service->findbyIds ( $fields, $items );
			$cvs = Shineisp_Commons_Utilities::cvsExport ( $service );
			die ( json_encode ( array ('mex' => '<a href="/public/documents/export.csv">' . $registry->Zend_Translate->translate ( "download" ) . '</a>' ) ) );
		}
		die ( json_encode ( array ('mex' => $this->translator->translate ( "There was a problem during the export process" ) ) ) );
	}
	
	/*
     *  bulkAction
     *  Execute a custom function for each item selected in the list
     *  this method will be call from a jQuery script 
     *  @return string
     */
	public function bulkAction() {
		$request = $this->getRequest ();
		$items = $request->getParams ();
		if (! empty ( $items ['params'] )) {
			parse_str ( $items ['params'], $arrparams );
			$action = isset ( $arrparams ['do'] ) ? $arrparams ['do'] : "";
			if (method_exists ( __CLASS__, $action )) {
				$retval = $this->$action ( $arrparams ['item'] );
				if ($retval) {
					die ( json_encode ( array ('mex' => $this->translator->translate ( "The task requested has been executed successfully." ) ) ) );
				}
			} else {
				die ( json_encode ( array ('mex' => $this->translator->translate ( "This feature has been not released yet" ) ) ) );
			}
		}
		die ( json_encode ( array ('mex' => $this->translator->translate ( "An error occurred during the task execution." ) ) ) );
	}
	
	/**
	 * dnsGrid
	 * Get the dns zone information.
	 * @return array
	 */
	private function dnsGrid() {
		$request = Zend_Controller_Front::getInstance ()->getRequest ();
		if (isset ( $request->id ) && is_numeric ( $request->id )) {
			$zones = new Dns_Zones ( );
			$records = $zones->findAllbyDomain ( $request->id, 'subdomain, zt.zone, target', true );
			if (isset ( $records [0] )) {
				return array ('records' => $records );
			}
		}
	}
}

