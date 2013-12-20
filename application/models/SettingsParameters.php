<?php

/**
 * SettingsParameters
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ShineISP
 * 
 * @author     Shine Software <info@shineisp.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class SettingsParameters extends BaseSettingsParameters {
	
	/**
	 * getList
	 * Get a list ready for the html select object
	 * @return array
	 */
	public static function getList($module = "", $empty = false) {
		$items = array ();
		$dq = Doctrine_Query::create ()->from ( 'SettingsParameters s' );
		
		if (! empty ( $module )) {
			$dq->where ( 's.module = ?', $module );
			$dq->orWhere ( "s.module = 'all'" );
		}
		
		$parameters = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		if ($empty) {
			$items [] = "";
		}
		
		foreach ( $parameters as $c ) {
			$items [$c ['parameter_id']] = $c ['name'];
		}
		
		return $items;
	}
	
	/**
	 * Get all settings information
	 * 
	 * @return array
	 */
	public static function getAllInfo() {
		$items = array ();
		$dq = Doctrine_Query::create ()->from ( 'SettingsParameters s' );
		
		$parameters = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		
		return $parameters;
	}
	
	/**
	 * Create the setting form and populate with the custom setting values
	 * 
	 * @param integer $groupid
	 */
	public static function createForm($groupid) {
		$form = new Zend_Form (array ('action' => '/admin/settings/index/groupid/' . $groupid, 'method' => 'post' ));
		$form->addElementPrefixPath('Shineisp_Decorator', 'Shineisp/Decorator/', 'decorator');
		
		$records = Doctrine_Query::create ()->from ( 'SettingsParameters s' )->where ( 'group_id = ?', $groupid )->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		if (! empty ( $records )) {
			foreach ( $records as $record ) {
				
				// Custom style added to the textareas
				$style = ( $record['type'] == "textarea") ? array('rows'=>4) : array();
				
				$form->addElement ( $record['type'], $record ['var'], $style + array ('decorators' => array('Bootstrap'), 'filters' => array ('StringTrim' ), 'label' => $record ['name'], 'description' => $record ['description'], 'class' => 'form-control obj_' . $record ['var'] ) );
					
				if(!empty($record ['config'])){
					$config = json_decode($record ['config'], true);
					
					if((!empty($config ['class']) && class_exists($config ['class'])) && !empty($config ['method']) && (method_exists($config ['class'], $config ['method']))){
						$class = $config ['class'];
						$method = $config ['method'];
						
						$data = call_user_func(array($class, $method));
						
						if(!empty($data)){
							if($record['type'] == "select"){
								$form->getElement($record ['var'])->setMultiOptions($data);
							}else{
								$form->getElement($record ['var'])->setValue($data);
							}
						}
					}
				}
			}
		}
		
		$settings = self::getValues(Settings::find_by_GroupId($groupid));
		$form->populate($settings);
		return $form;
	}
	
	/**
	 * getValues
	 * Prepare the records in order to use in the setting form
	 * @param unknown_type $records
	 */
	private static function getValues($records){
		$fixedrecords = array();
		if(!empty($records)){
			foreach ($records as $record){
				$fixedrecords[$record['variable']] = $record['value']; 
			}
		}
		return $fixedrecords;
	}
	
	/**
     * getParameterbyVar
     * Get a parameter by name
     * @param $var
     * @return Doctrine 
     */
    public static function getParameterbyVar($var) {
        $dq = Doctrine_Query::create ()
                          ->from ( 'SettingsParameters p' )
                          ->where ( "p.var = ?", $var );
                          
        return $dq->fetchOne();
    }	
	
	/**
     * Get a parameter by its group name and its var name
     * @param $groupname
     * @param $var
     * @return Array 
     */
    public static function getParameterbyGroupNameAndVar($groupname, $var) {
        $dq = Doctrine_Query::create ()
                          ->from ( 'SettingsParameters p' )
                          ->leftJoin ( 'p.SettingsGroups sg' )
                          ->leftJoin ( 'p.Settings s' )
                          ->where ( "sg.name = ?", $groupname )
                          ->addWhere ( "p.var = ?", $var );
                          
        $records = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
        $records = isset($records[0]) ? $records[0] : null; 
        return $records;
    }	
    
	/**
     * getParameterbyGroupID
     * Get a parameter by name
     * @param $var
     * @return Array 
     */
    public static function getParameterbyGroupID($groupID, $fields="*") {
        $dq = Doctrine_Query::create ()->select($fields)
                          ->from ( 'SettingsParameters p' )
                          ->where ( "p.group_id = ?", $groupID );
                          
        $records = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
        return $records;
    }	
    
	/**
     * getParameterIDsByGroupId
     * Get a parameter by name
     * @param $var
     * @return Array 
     */
    public static function getParameterIDsByGroupId($groupID, $fields="*") {
        $items = array();
    	$dq = Doctrine_Query::create ()->select($fields)
                          ->from ( 'SettingsParameters p' )
                          ->where ( "p.group_id = ?", $groupID );
                          
        $records = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
    
        foreach ( $records as $c ) {
			$items [] = $c ['parameter_id'];
		}
		
        return $items;
    }	

    /**
     * removeParam
     * Delete a parameter and its value
     * @param integer $id
     */
    
    public static function removeParam($id) {
    	
    	Doctrine::getTable ( 'SettingsParameters' )->find ($id)->delete ();
    	 
    	// Refresh all the parameters
    	self::loadParams(null, true);
    	
    	return true;
    }
    

    /**
     * Load all the params
     * @param $parameter
     * @param $module
     * @param $isp
     * @return Doctrine Record
     */
    public static function loadParams($module="Default") {
		$session = new Zend_Session_Namespace ( $module );
		
		$registry = Shineisp_Registry::get('ISP');
		
		$isp = !empty($registry) && is_object($registry) ? $registry->isp_id : 1;
		
    	$dq = Doctrine_Query::create ()->from ( 'Settings s' )
								    	->leftJoin ( 's.SettingsParameters p' )
								    	->where ( "s.isp_id = ?", $isp );
    
    	$records = $dq->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
    	
    	$parameters = array();
    	foreach ($records as $record){
    		$parameters[$record['SettingsParameters']['var']] = $record['value'];
    	}
    	
    	$session->parameters = $parameters;
    	 
    	return $parameters;
    }
    
	/**
	 * addParam
	 * Add a new parameter
	 * @param string $label
	 * @param string $description
	 * @param string $var
	 * @param string $type
	 * @param string $module
	 * @param boolean $enabled
	 * @param integer $groupID
	 */
    public static function addParam($label, $description, $var, $type, $module, $enabled, $groupID, $config=array()) {
    	
    	$p = self::getParameterbyVar($var);
    	
    	if(!empty($p)){
    		return $p->get('parameter_id');
    	}
    	
    	$parameter = new SettingsParameters();
    	$parameter['name'] = $label;
    	$parameter['description'] = $description;
    	$parameter['var'] = $var;
    	$parameter['type'] = $type;
    	$parameter['module'] = $module;
    	$parameter['enabled'] = $enabled;
    	$parameter['group_id'] = $groupID;
    	$parameter['config'] = json_encode($config);
    	
    	$parameter->save();
    	
    	return $parameter['parameter_id'];
    }
    
}