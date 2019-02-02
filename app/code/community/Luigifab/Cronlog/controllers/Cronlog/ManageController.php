<?php
/**
 * Created S/31/05/2014
 * Updated M/15/01/2019
 *
 * Copyright 2012-2019 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Cronlog_Cronlog_ManageController extends Mage_Adminhtml_Controller_Action {

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('tools/cronlog');
	}

	public function indexAction() {
		$this->loadLayout()->_setActiveMenu('tools/cronlog')->renderLayout();
	}

	public function saveAction() {

		$this->setUsedModuleName('Luigifab_Cronlog');

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

				$jobs = Mage::getResourceModel('cron/schedule_collection');
				$jobs->addFieldToFilter('job_code', $code);
				$jobs->addFieldToFilter('status', 'pending');

				foreach ($jobs as $job)
					$job->delete();
			}
		}
		catch (Exception $e) {
			$msg = '<li class="error-msg"><ul><li>'.$e->getMessage().'</li></ul></li>';
		}

		Mage::getConfig()->reinit(); // très important

		$msg = !empty($msg) ? '<div id="messages" onclick="this.parentNode.removeChild(this);"><ul class="messages">'.$msg.'</ul></div> ' : '';
		$blk = $this->getLayout()->createBlock('cronlog/adminhtml_manage_grid')->toHtml();

		$this->getResponse()->setBody($msg.$blk);
	}
}