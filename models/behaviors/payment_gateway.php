<?php
/* SVN FILE: $Id: tree.php 8120 2009-03-19 20:25:10Z gwoo $ */
/**
 * Tree behavior class.
 *
 * Enables a model object to act as a node-based tree.
 *
 * PHP versions 4 and 5
 *
 * CakePHP :  Rapid Development Framework (http://www.cakephp.org)
 * Copyright 2006-2008, Cake Software Foundation, Inc.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright 2006-2008, Cake Software Foundation, Inc.
 * @link          http://www.cakefoundation.org/projects/info/cakephp CakePHP Project
 * @package       cake
 * @subpackage    cake.cake.libs.model.behaviors
 * @since         CakePHP v 1.2.0.4487
 * @version       $Revision: 8120 $
 * @modifiedby    $LastChangedBy: gwoo $
 * @lastmodified  $Date: 2009-03-19 13:25:10 -0700 (Thu, 19 Mar 2009) $
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Tree Behavior.
 *
 * Enables a model object to act as a node-based tree. Using Modified Preorder Tree Traversal
 * 
 * @see http://en.wikipedia.org/wiki/Tree_traversal
 * @package       cake
 * @subpackage    cake.cake.libs.model.behaviors
 */
class PaymentGatewayBehavior extends ModelBehavior {

	/**
	 * Errors
	 *
	 * @var array
	 */
	var $errors = array();
	/**
	 * Defaults
	 *
	 * @var array
	 * @access protected
	 */
	var $_defaults = array();	
	/**
	 * User Model
	 *
	 * @var array
	 * @access protected
	 */
	var $triggers = array();

	
	var $relationships = array(
		/*'belongsTo'=> array(
			'Tax' => array('className' => 'Cart.CartTax'),				
			'Shipping'=> array('className' => 'Cart.CartShipping'),
		)*/
	);
	

	/**
	 * Initiate Installation behavior
	 *
	 * @param object $Model instance of model
	 * @param array $fields array of configuration settings.
	 * @return void
	 * @access public
	 */
	function setup(&$Model, $settings) {
		$this->gateway = ConnectionManager::getDataSource($settings['gateway']);
		if (isset($settings['logIpn']) && $settings['logIpn']) {
			$this->logIpn = $settings['logIpn'];
		}
		//$this->bindRelationships($Model, $settings['gateway']);
	}
	
	function beforeSave(&$Model, $created) {
		if (!$this->_trigger('beforePayment')) {
			return false;
		}
		if ($created) {
			$gateway = $this->gateway;
	        $response = $gateway->create($billing, $payment);
		}
		$this->_trigger('afterPayment');
	}
	
	function beforeDelete(&$Model) {
		if ($this->_trigger('beforeRefund')) {
			return false;
		}
		$gateway = $this->gateway;
	    $response = $gateway->delete($billing, $payment);
	    $success = true;
		$this->_trigger('afterRefund');
		return $success;
	}
	
	function beforeFind(&$Model, $results, $primary) {
		
		$gateway = $this->gateway;
	    $response = $gateway->read($billing, $payment);
	}
	
	/**
	 * Bind all related models in the plugin to the User model
	 *
	 * @return boolean success
	 * @access public
	 */
	function bindRelationships(&$Model, $gateway = null	) {
		//$success = $Model->bindModel($this->relationships, false);
		// @TODO Proper binding of relationships on initialization
		$success = true;
		return $success;
	}
	
	/**
	 * Checks if the developer declared the trigger in the model before calling it
	 *
	 * @param object $Model instance of model
	 * @param string $trigger name of trigger to call
	 * @access protected
	 */
	function _trigger(&$Model, $trigger) {
		if (method_exists($Model, $trigger)) {
			return call_user_func(array($Model, $trigger));
		}
	}
}
?>