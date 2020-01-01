<?php
/**
 * Created D/10/02/2013
 * Updated M/20/08/2019
 *
 * Copyright 2012-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/magento/cronlog
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL) as published
 * by the free software foundation, either version 2 of the license, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but without any warranty, without even the implied warranty of
 * merchantability or fitness for a particular purpose. See the
 * GNU General Public License (GPL) for more details.
 */

class Luigifab_Cronlog_Block_Adminhtml_History_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

	public function __construct() {

		parent::__construct();

		$this->_controller = 'adminhtml_history';
		$this->_blockGroup = 'cronlog';
		$this->_headerText = $this->__('New cron job');

		$this->_removeButton('reset');
		$this->_removeButton('delete');
		$this->_updateButton('save', 'label', $this->__('Add'));

		if (!empty($id = $this->getRequest()->getParam('id')) && !empty($this->getRequest()->getParam('code')))
			$this->_updateButton('back', 'onclick', "setLocation('".$this->getUrl('*/*/view', ['id' => $id])."');");
	}
}