<?php
/**
 * Created W/29/02/2012
 * Updated J/26/07/2012
 * Version 2
 *
 * Copyright 2012 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * https://redmine.luigifab.info/projects/magento/wiki/cronlog
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

class Luigifab_Cronlog_Cronlog_HistoryController extends Mage_Adminhtml_Controller_Action {

	protected function _initAction() {
		$this->loadLayout()->_setActiveMenu('tools/cronlog');
		return $this;
	}

	public function indexAction() {
		$this->_initAction()->renderLayout();
	}

	public function showAction() {

		$cron = Mage::getModel('cron/schedule')->load($this->getRequest()->getParam('id'));

		if (is_numeric($cron->getScheduleId()) && ($cron->getScheduleId() != 0))
			$this->_initAction()->renderLayout();
		else
			$this->_redirect('*/*/index');
	}
}