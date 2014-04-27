<?php
/**
 * Created W/29/02/2012
 * Updated S/26/04/2014
 * Version 10
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

class Luigifab_Cronlog_Cronlog_HistoryController extends Mage_Adminhtml_Controller_Action {

	public function indexAction() {

		if ($this->getRequest()->getParam('isAjax', false))
			$this->getResponse()->setBody($this->getLayout()->createBlock('cronlog/adminhtml_history_grid')->toHtml());
		else
			$this->loadLayout()->_setActiveMenu('tools/cronlog')->renderLayout();
	}

	public function newAction() {

		$this->loadLayout();
		$this->_setActiveMenu('tools/cronlog');

		$this->_addLeft($this->getLayout()->createBlock('cronlog/adminhtml_history_edit_tabs'));
		$this->_addContent($this->getLayout()->createBlock('cronlog/adminhtml_history_edit'));

		$this->renderLayout();
	}

	public function viewAction() {

		$id = intval($this->getRequest()->getParam('id', 0));

		if ($id > 0) {

			$job = Mage::getModel('cron/schedule')->load($id);

			if ($job->getId() > 0) {
				Mage::register('current_job', $job);
				$this->loadLayout()->_setActiveMenu('tools/cronlog')->renderLayout();
			}
			else {
				$this->_redirect('*/*/index');
			}
		}
		else {
			$this->_redirect('*/*/index');
		}
	}

	public function saveAction() {

		try {
			if (Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {
				$this->_redirect('*/*/index');
			}
			else {
				// préparation
				if (($code = $this->getRequest()->getPost('job_code', false)) === false)
					throw new Exception(Mage::helper('cronlog')->__('The <em>job_code</em> field is a required value.'));

				// dates
				$dateCreated = Mage::app()->getLocale()->date();
				$dateCreated->setTimezone(Mage::getStoreConfig('general/locale/timezone'));

				$dateScheduled = Mage::app()->getLocale()->date();
				$dateScheduled->setTimezone(Mage::getStoreConfig('general/locale/timezone'));
				$dateScheduled->addMinute($this->getRequest()->getPost('scheduled_at', 1));

				// enregistrement
				$job = Mage::getModel('cron/schedule');
				$job->setJobCode($code);
				$job->setCreatedAt(Mage::helper('cronlog')->getDateToUtc($dateCreated->toString(Zend_Date::RFC_3339)));
				$job->setScheduledAt(Mage::helper('cronlog')->getDateToUtc($dateScheduled->toString(Zend_Date::RFC_3339)));
				$job->save();

				$id = $job->getId();

				// redirection
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cronlog')->__('Job number %d has been successfully scheduled.', $id));
				$this->_redirect('*/*/index');
			}
		}
		catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->setFormData($this->getRequest()->getPost());
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			$this->_redirect('*/*/new');
		}
	}

	public function cancelAction() {

		try {
			if (Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {
				$this->_redirect('*/*/index');
			}
			else {
				// préparation
				if (($id = $this->getRequest()->getParam('id', false)) === false)
					throw new Exception(Mage::helper('cronlog')->__('The <em>job_id</em> field is a required value.'));

				// suppression
				$job = Mage::getModel('cron/schedule')->load($id);
				$job->delete();

				// redirection
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cronlog')->__('Job number %d has been successfully deleted.', $id));
				$this->_redirect('*/*/index');
			}
		}
		catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			$this->_redirect('*/*/index');
		}
	}
}