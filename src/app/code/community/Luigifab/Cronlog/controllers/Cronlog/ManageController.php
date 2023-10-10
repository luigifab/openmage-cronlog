<?php
/**
 * Created S/31/05/2014
 * Updated L/05/06/2023
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

class Luigifab_Cronlog_Cronlog_ManageController extends Mage_Adminhtml_Controller_Action {

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('tools/cronlog');
	}

	public function getUsedModuleName() {
		return 'Luigifab_Cronlog';
	}

	public function loadLayout($ids = null, $generateBlocks = true, $generateXml = true) {
		parent::loadLayout($ids, $generateBlocks, $generateXml);
		$this->_title($this->__('Tools'))->_title($this->__('Cron jobs'))->_setActiveMenu('tools/cronlog');
		return $this;
	}

	public function indexAction() {
		$this->loadLayout()->renderLayout();
	}

	public function saveAction() {

		try {
			if (empty($code = $this->getRequest()->getParam('code')))
				Mage::throwException($this->__('The <em>%s</em> field is a required field.', 'code'));

			if (is_string(Mage::getStoreConfig('crontab/jobs/'.$code.'/schedule/disabled'))) {

				$txt = $this->__('Job <strong>%s</strong> has been successfully <strong>enabled</strong>.', $code);
				$msg = '<li class="success-msg"><ul><li>'.$txt.'</li></ul></li>';

				Mage::getModel('core/config')->deleteConfig('crontab/jobs/'.$code.'/schedule/disabled');
				Mage::log(strip_tags($txt), Zend_Log::INFO, 'cronlog.log');
			}
			else {
				$txt = $this->__('Job <strong>%s</strong> has been successfully <strong>disabled</strong>.', $code);
				$msg = '<li class="success-msg"><ul><li>'.$txt.'</li></ul></li>';

				Mage::getModel('core/config')->saveConfig('crontab/jobs/'.$code.'/schedule/disabled', '1');
				Mage::log(strip_tags($txt), Zend_Log::INFO, 'cronlog.log');

				$jobs = Mage::getResourceModel('cron/schedule_collection')
					->addFieldToFilter('job_code', $code)
					->addFieldToFilter('status', 'pending')
					->load(); // for getIterator

				foreach ($jobs->getIterator() as $job)
					$job->delete();
			}
		}
		catch (Throwable $t) {
			$msg = '<li class="error-msg"><ul><li>'.$t->getMessage().'</li></ul></li>';
		}

		Mage::getConfig()->reinit(); // très important

		$msg = empty($msg) ? '' : '<div id="messages" onclick="this.remove();"><ul class="messages">'.$msg.'</ul></div> ';
		$blk = $this->getLayout()->createBlock('cronlog/adminhtml_manage_grid')->toHtml();

		$this->getResponse()->setBody($msg.$blk);
	}
}