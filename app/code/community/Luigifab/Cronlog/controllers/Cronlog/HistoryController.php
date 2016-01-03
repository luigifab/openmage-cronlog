<?php
/**
 * Created W/29/02/2012
 * Updated S/10/10/2015
 * Version 19
 *
 * Copyright 2012-2016 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('tools/cronlog');
	}

	public function indexAction() {

		if (!is_null($this->getRequest()->getParam('isAjax')))
			$this->getResponse()->setBody($this->getLayout()->createBlock('cronlog/adminhtml_history_grid')->toHtml());
		else
			$this->loadLayout()->_setActiveMenu('tools/cronlog')->renderLayout();
	}

	public function newAction() {

		Mage::getConfig()->reinit();

		$this->loadLayout();
		$this->_setActiveMenu('tools/cronlog');

		$this->_addLeft($this->getLayout()->createBlock('cronlog/adminhtml_history_edit_tabs'));
		$this->_addContent($this->getLayout()->createBlock('cronlog/adminhtml_history_edit'));

		$this->renderLayout();
	}

	public function viewAction() {

		$job = Mage::getModel('cron/schedule')->load(intval($this->getRequest()->getParam('id', 0)));

		if ($job->getId() > 0) {
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

				if (($code = $this->getRequest()->getPost('job_code', false)) === false)
					Mage::throwException($this->__('The <em>%s</em> field is a required value.', 'job_code'));

				$dateCreated = Mage::app()->getLocale()->date();
				$dateCreated->setTimezone(Mage::getStoreConfig('general/locale/timezone'));
				$dateCreated = Mage::helper('cronlog')->getDateToUtc($dateCreated->toString(Zend_Date::RFC_3339));

				$dateScheduled = Mage::app()->getLocale()->date();
				$dateScheduled->setTimezone(Mage::getStoreConfig('general/locale/timezone'));
				$dateScheduled->addMinute($this->getRequest()->getPost('scheduled_at', 1));
				$dateScheduled = Mage::helper('cronlog')->getDateToUtc($dateScheduled->toString(Zend_Date::RFC_3339));

				$job = Mage::getModel('cron/schedule');
				$job->setJobCode($code);
				$job->setCreatedAt($dateCreated);
				$job->setScheduledAt($dateScheduled);
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

				if ((($id = $this->getRequest()->getParam('id', false)) === false) || !is_numeric($id))
					Mage::throwException($this->__('The <em>%s</em> field is a required value.', 'id'));

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

				if ((($id = $this->getRequest()->getParam('id', false)) === false) || !is_numeric($id))
					Mage::throwException($this->__('The <em>%s</em> field is a required value.', 'id'));

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