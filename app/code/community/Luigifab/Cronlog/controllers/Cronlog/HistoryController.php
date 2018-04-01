<?php
/**
 * Created W/29/02/2012
 * Updated M/27/02/2018
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

class Luigifab_Cronlog_Cronlog_HistoryController extends Mage_Adminhtml_Controller_Action {

	protected function _validateSecretKey() {

		$result = parent::_validateSecretKey();

		if (Mage::getSingleton('admin/session')->isLoggedIn() && ($this->getFullActionName() == 'adminhtml_cronlog_history_view') && !$result) {
			$this->getRequest()->setParam(Mage_Adminhtml_Model_Url::SECRET_KEY_PARAM_NAME, Mage::getSingleton('adminhtml/url')->getSecretKey());
			$result = parent::_validateSecretKey();
		}

		return $result;
	}

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('tools/cronlog');
	}

	public function indexAction() {

		if ($this->getRequest()->isXmlHttpRequest() || !empty($this->getRequest()->getParam('isAjax')))
			$this->getResponse()->setBody($this->getLayout()->createBlock('cronlog/adminhtml_history_grid')->toHtml());
		else
			$this->loadLayout()->_setActiveMenu('tools/cronlog')->renderLayout();
	}

	public function newAction() {
		$this->loadLayout()->_setActiveMenu('tools/cronlog')->renderLayout();
		Mage::getSingleton('adminhtml/session')->unsFormData();
	}

	public function viewAction() {

		$job = Mage::getModel('cron/schedule')->load(intval($this->getRequest()->getParam('id')));

		if (!empty($job->getId())) {
			Mage::register('current_job', $job);
			$this->loadLayout()->_setActiveMenu('tools/cronlog')->renderLayout();
		}
		else {
			$this->_redirect('*/*/index');
		}
	}

	public function saveAction() {

		$this->setUsedModuleName('Luigifab_Cronlog');
		$redirect = '*/*/index';

		try {
			if (!Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {

				if (empty($code = $this->getRequest()->getPost('job_code')))
					Mage::throwException($this->__('The <em>%s</em> field is a required field.', 'job_code'));

				$dateScheduled = Mage::getSingleton('core/locale')->date();
				$dateScheduled->setTimezone('UTC');
				$dateScheduled->addMinute($this->getRequest()->getPost('scheduled_at', 1));

				$job = Mage::getModel('cron/schedule');
				$job->setData('job_code', $code);
				$job->setData('created_at', date('Y-m-d H:i:s'));
				$job->setData('scheduled_at', $dateScheduled->toString(Zend_Date::RFC_3339));
				$job->save();

				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Job number %d has been successfully scheduled.', $job->getId()));
			}
		}
		catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->setFormData($this->getRequest()->getPost());
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			$redirect = '*/*/new';
		}

		$this->_redirect($redirect);
	}

	public function cancelAction() {

		$this->setUsedModuleName('Luigifab_Cronlog');

		try {
			if (!Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {

				if (empty($id = $this->getRequest()->getParam('id')) || !is_numeric($id))
					Mage::throwException($this->__('The <em>%s</em> field is a required field.', 'id'));

				Mage::getModel('cron/schedule')->load($id)->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Job number %d has been successfully canceled.', $id));
			}
		}
		catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}

		$this->_redirect('*/*/index');
	}

	public function deleteAction() {

		$this->setUsedModuleName('Luigifab_Cronlog');

		try {
			if (!Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {

				if (empty($id = $this->getRequest()->getParam('id')) || !is_numeric($id))
					Mage::throwException($this->__('The <em>%s</em> field is a required field.', 'id'));

				Mage::getModel('cron/schedule')->load($id)->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Job number %d has been successfully deleted.', $id));
			}
		}
		catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}

		$this->_redirect('*/*/index');
	}
}