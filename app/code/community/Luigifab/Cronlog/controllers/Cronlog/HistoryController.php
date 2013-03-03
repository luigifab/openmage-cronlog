<?php
/**
 * Created W/29/02/2012
 * Updated D/03/03/2013
 * Version 7
 *
 * Copyright 2012-2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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
		$this->loadLayout();
		$this->_setActiveMenu('tools/cronlog');
		$this->renderLayout();
	}

	public function newAction() {
		$this->loadLayout();
		$this->_setActiveMenu('tools/cronlog');
		$this->_addLeft($this->getLayout()->createBlock('cronlog/adminhtml_history_edit_tabs'));
		$this->_addContent($this->getLayout()->createBlock('cronlog/adminhtml_history_edit'));
		$this->renderLayout();
	}

	public function viewAction() {

		$id = $this->getRequest()->getParam('id', 0);

		if (is_numeric($id) && ($id > 0)) {

			$job = Mage::getModel('cron/schedule')->load($id);

			if (is_numeric($job->getId()) && ($job->getId() > 0)) {

				Mage::register('current_job', $job);

				$this->loadLayout();
				$this->_setActiveMenu('tools/cronlog');
				$this->renderLayout();
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
			// préparation
			$code = $this->getRequest()->getPost('job_code', false);

			if ($code === false)
				throw new Exception(Mage::helper('cronlog')->__('The <em>job_code</em> field is a required value.'));

			// dates
			$dateCreated = Mage::app()->getLocale()->date();
			$dateCreated->setTimezone('Etc/UTC');

			$dateScheduled = Mage::app()->getLocale()->date();
			$dateScheduled->addMinute($this->getRequest()->getPost('scheduled_at', 1));
			$dateScheduled->setTimezone('Etc/UTC');

			// enregistrement
			$job = Mage::getModel('cron/schedule');
			$job->setJobCode($code);
			$job->setCreatedAt($dateCreated);
			$job->setScheduledAt($dateScheduled);
			$job->save();

			// redirection
			Mage::getSingleton('adminhtml/session')->addSuccess(
				Mage::helper('cronlog')->__('Job number %d has been successfully scheduled.', $job->getId()));

			$this->_redirect('*/*/index');
		}
		catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			Mage::getSingleton('adminhtml/session')->setFormData($this->getRequest()->getPost());
			$this->_redirect('*/*/new');
		}
	}

	public function cancelAction() {

		try {
			// préparation
			$id = $this->getRequest()->getParam('id', false);

			if ($id === false)
				throw new Exception(Mage::helper('cronlog')->__('The <em>job_id</em> field is a required value.'));

			// suppression
			$job = Mage::getModel('cron/schedule')->load($id);
			$job->delete();

			// redirection
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cronlog')->__('Job number %d has been successfully deleted.', $id));
			$this->_redirect('*/*/index');
		}
		catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			$this->_redirect('*/*/index');
		}
	}
}