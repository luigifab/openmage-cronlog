<?php
/**
 * Created D/10/02/2013
 * Updated D/11/12/2022
 *
 * Copyright 2012-2023 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://github.com/luigifab/openmage-cronlog
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

		if (!empty($id = (int) $this->getRequest()->getParam('id', 0)) && !empty($this->getRequest()->getParam('code'))) {
			$this->_updateButton('back', 'onclick', "setLocation('".$this->getUrl('*/*/view', ['id' => $id])."');");
		}
		else {
			$this->_addButton('save_and_continue', [
				'label'   => $this->__('Add and Continue'),
				'onclick' => "editForm.submit(editForm.validator.form.getAttribute('action') + 'back/new/');",
				'class'   => 'save',
			], 1);
		}
	}
}