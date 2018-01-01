<?php
/**
 * Created D/10/02/2013
 * Updated J/07/12/2017
 *
 * Copyright 2012-2018 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * https://www.luigifab.info/magento/cronlog
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

class Luigifab_Cronlog_Block_Adminhtml_History_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

	public function __construct() {

		parent::__construct();

		$this->setId('cronlogTabs');
		$this->setTitle($this->__('Job'));
		$this->setDestElementId('edit_form');
	}

	protected function _prepareLayout() {

		$this->addTab('general_section', array(
			'label'   => $this->__('Job'),
			'content' => $this->getLayout()->createBlock('cronlog/adminhtml_history_edit_tab_general')->toHtml(),
			'active'  => true
		));

		return parent::_prepareLayout();
	}
}