<?php
/**
 * Created S/31/05/2014
 * Updated L/15/05/2017
 *
 * Copyright 2012-2017 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Cronlog_Cronlog_ConfigController extends Mage_Adminhtml_Controller_Action {

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('tools/cronlog');
	}

	public function indexAction() {
		Mage::getConfig()->reinit(); // très important
		$this->loadLayout()->_setActiveMenu('tools/cronlog')->renderLayout();
	}

	public function saveAction() {

		$this->setUsedModuleName('Luigifab_Cronlog');

		try {
			if (empty($code = $this->getRequest()->getParam('code')))
				Mage::throwException($this->__('The <em>%s</em> field is a required field.', 'code'));

			if (is_string(Mage::getStoreConfig('crontab/jobs/'.$code.'/schedule/disabled'))) {
				$text = $this->__('Job <strong>%s</strong> has been successfully <strong>enabled</strong>', $code);
				Mage::getModel('core/config')->deleteConfig('crontab/jobs/'.$code.'/schedule/disabled');
				Mage::log(strip_tags($text), Zend_Log::INFO, 'cronlog.log');
				$msg = '<li class="success-msg"><ul><li>'.$text.'</li></ul></li>';
			}
			else {
				$text = $this->__('Job <strong>%s</strong> has been successfully <strong>disabled</strong>', $code);
				Mage::getModel('core/config')->saveConfig('crontab/jobs/'.$code.'/schedule/disabled', '1');
				Mage::log(strip_tags($text), Zend_Log::INFO, 'cronlog.log');
				$msg = '<li class="success-msg"><ul><li>'.$text.'</li></ul></li>';
			}
		}
		catch (Exception $e) {
			$msg = '<li class="error-msg"><ul><li>'.$e->getMessage().'</li></ul></li>';
		}

		Mage::getConfig()->reinit(); // très important

		$msg = (!empty($msg)) ? '<div id="messages" onclick="this.parentNode.removeChild(this);"><ul class="messages">'.$msg.'</ul></div> ' : '';
		$this->getResponse()->setBody($msg.$this->getLayout()->createBlock('cronlog/adminhtml_config_grid')->toHtml());
	}
}