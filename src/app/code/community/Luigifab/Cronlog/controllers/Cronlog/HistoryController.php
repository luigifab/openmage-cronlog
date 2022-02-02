<?php
/**
 * Created W/29/02/2012
 * Updated J/27/01/2022
 *
 * Copyright 2012-2022 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/openmage/cronlog
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

		if (!$result && ($this->getFullActionName() == 'adminhtml_cronlog_history_view')) {
			$this->getRequest()->setParam(Mage_Adminhtml_Model_Url::SECRET_KEY_PARAM_NAME, Mage::getSingleton('adminhtml/url')->getSecretKey());
			$result = parent::_validateSecretKey();
		}

		return $result;
	}

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('tools/cronlog');
	}

	public function getUsedModuleName() {
		return 'Luigifab_Cronlog';
	}

	public function loadLayout($ids = null, $generateBlocks = true, $generateXml = true) {
		$this->_title($this->__('Tools'))->_title($this->__('Cron jobs'));
		parent::loadLayout($ids, $generateBlocks, $generateXml);
		$this->_setActiveMenu('tools/cronlog');
		return $this;
	}

	public function indexAction() {

		if ($this->getRequest()->isXmlHttpRequest() || !empty($this->getRequest()->getParam('isAjax')))
			$this->getResponse()->setBody($this->getLayout()->createBlock('cronlog/adminhtml_history_grid')->toHtml());
		else
			$this->loadLayout()->renderLayout();
	}

	public function newAction() {

		$this->loadLayout()->renderLayout();
		Mage::getSingleton('adminhtml/session')->unsFormData();
	}

	public function viewAction() {

		$cron = Mage::getModel('cron/schedule')->load((int) $this->getRequest()->getParam('id', 0));

		if (!empty($cron->getId())) {
			Mage::register('current_job', $cron);
			$this->loadLayout()->renderLayout();
		}
		else {
			$this->_redirect('*/*/index');
		}
	}

	public function saveAction() {

		try {
			if (Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {
				$this->_redirect('*/*/new');
			}
			else {
				if (empty($code = $this->getRequest()->getPost('job_code')))
					Mage::throwException($this->__('The <em>%s</em> field is a required field.', 'job_code'));

				$dateScheduled = Mage::getSingleton('core/locale')->date();
				$dateScheduled->setTimezone('UTC');
				$dateScheduled->addMinute($this->getRequest()->getPost('scheduled_at', 1));

				$cron = Mage::getModel('cron/schedule');
				$cron->setData('job_code', $code);
				$cron->setData('created_at', date('Y-m-d H:i:s'));
				$cron->setData('scheduled_at', $dateScheduled->toString(Zend_Date::RFC_3339));

				if (!empty($old = (int) $this->getRequest()->getParam('id', 0))) {
					$old = Mage::getModel('cron/schedule')->load($old);
					if ($old->getData('job_code') == $code)
						$cron->setData('messages', $old->getData('messages'));
				}

				$cron->save();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Job number %d has been successfully scheduled.', $cron->getId()));

				if (empty($this->getRequest()->getParam('back')))
					$this->_redirect('*/*/view', ['id' => $cron->getId()]);
				else
					$this->_redirect('*/*/new', ['code' => $code]);
			}
		}
		catch (Throwable $t) {
			Mage::getSingleton('adminhtml/session')->setFormData($this->getRequest()->getPost());
			Mage::getSingleton('adminhtml/session')->addError($t->getMessage());
			$this->_redirect('*/*/new');
		}
	}

	public function runAction() {

		$cron = Mage::getModel('cron/schedule')->load((int) $this->getRequest()->getParam('id', 0));

		if (!empty($cron->getId()) && ($cron->getData('status') == 'pending')) {

			$dir = Mage::getBaseDir('log');
			if (!is_dir($dir))
				@mkdir($dir, 0755);

			exec(sprintf('php %s %d %d >> %s 2>&1 &',
				str_replace('Cronlog/etc', 'Cronlog/lib/run.php', Mage::getModuleDir('etc', 'Luigifab_Cronlog')),
				$cron->getId(),
				Mage::getIsDeveloperMode() ? 1 : 0,
				$dir.'/cron.log'));

			sleep(2);

			if ($cron->load($cron->getId())->getData('status') != 'pending')
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Job number %d has been successfully started.', $cron->getId()));

			$this->_redirect('*/*/view', ['id' => $cron->getId()]);
		}
		else if (!empty($cron->getId())) {
			$this->_redirect('*/*/view', ['id' => $cron->getId()]);
		}
		else {
			$this->_redirect('*/*/index');
		}
	}

	public function cancelAction() {

		try {
			if (!Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {

				if (empty($id = $this->getRequest()->getParam('id')) || !is_numeric($id))
					Mage::throwException($this->__('The <em>%s</em> field is a required field.', 'id'));

				Mage::getModel('cron/schedule')->load($id)->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Job number %d has been successfully canceled.', $id));
			}
		}
		catch (Throwable $t) {
			Mage::getSingleton('adminhtml/session')->addError($t->getMessage());
		}

		$this->_redirect('*/*/index');
	}

	public function deleteAction() {

		try {
			if (!Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {

				if (empty($id = $this->getRequest()->getParam('id')) || !is_numeric($id))
					Mage::throwException($this->__('The <em>%s</em> field is a required field.', 'id'));

				Mage::getModel('cron/schedule')->load($id)->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Job number %d has been successfully deleted.', $id));
			}
		}
		catch (Throwable $t) {
			Mage::getSingleton('adminhtml/session')->addError($t->getMessage());
		}

		$this->_redirect('*/*/index');
	}
}