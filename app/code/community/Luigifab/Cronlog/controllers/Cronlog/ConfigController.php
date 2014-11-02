<?php
/**
 * Created S/31/05/2014
 * Updated S/04/10/2014
 * Version 8
 *
 * Copyright 2012-2014 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Cronlog_Cronlog_ConfigController extends Mage_Adminhtml_Controller_Action {

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('tools/cronlog');
	}

	public function indexAction() {
		$this->loadLayout()->_setActiveMenu('tools/cronlog')->renderLayout();
	}

	public function saveAction() {

		$this->setUsedModuleName('Luigifab_Cronlog');
		$msg = array();

		try {
			if (($code = $this->getRequest()->getParam('code', false)) === false)
				Mage::throwException($this->__('The <em>%s</em> field is a required value.', 'code'));

			if (is_string(Mage::getStoreConfig('crontab/jobs/'.$code.'/schedule/disabled'))) {
				$text = $this->__('Job <b>%s</b> has been successfully <b>enabled</b>', $code);
				Mage::getModel('core/config')->deleteConfig('crontab/jobs/'.$code.'/schedule/disabled');
				Mage::log(strip_tags($text), Zend_Log::NOTICE, 'cronlog.log');
				$msg[] = '<li class="success-msg"><ul><li>'.$text.'</li></ul></li>';
			}
			else {
				Mage::getModel('core/config')->saveConfig('crontab/jobs/'.$code.'/schedule/disabled', '1');
				$text = $this->__('Job <b>%s</b> has been successfully <b>disabled</b>', $code);
				Mage::log(strip_tags($text), Zend_Log::NOTICE, 'cronlog.log');
				$msg[] = '<li class="success-msg"><ul><li>'.$text.'</li></ul></li>';
			}
		}
		catch (Exception $e) {
			$msg[] = '<li class="error-msg"><ul><li>'.$e->getMessage().'</li></ul></li>';
		}

		$msg = (!empty($msg)) ? '<div id="messages" onclick="this.parentNode.removeChild(this);"><ul class="messages">'.implode($msg).'</ul></div> ' : '';
		$this->getResponse()->setBody($msg.$this->getLayout()->createBlock('cronlog/adminhtml_config_grid')->toHtml());
	}
}