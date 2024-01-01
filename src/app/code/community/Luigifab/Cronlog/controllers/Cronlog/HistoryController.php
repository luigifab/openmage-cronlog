<?php
/**
 * Created W/29/02/2012
 * Updated J/28/12/2023
 *
 * Copyright 2012-2024 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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
		parent::loadLayout($ids, $generateBlocks, $generateXml);
		$this->_title($this->__('Tools'))->_title($this->__('Cron jobs'))->_setActiveMenu('tools/cronlog');
		return $this;
	}

	public function indexAction() {

		if ($this->getRequest()->isXmlHttpRequest() || !empty($this->getRequest()->getParam('isAjax')))
			$this->getResponse()->setBody($this->getLayout()->createBlock('cronlog/adminhtml_history_grid')->toHtml());
		else
			$this->loadLayout()->renderLayout();
	}

	public function previewAction() {

		$this->loadLayout();
		$block = $this->getLayout()->createBlock('adminhtml/widget_button')
			->setData('type', 'button')
			->setData('label', $this->__('Back'))
			->setData('class', 'back')
			->setData('onclick', 'setLocation(\''.$this->getUrl('*/system_config/edit', ['section' => 'cronlog']).'\');');

		$html  = '<div class="content-header"><table cellspacing="0"><tbody><tr><td><h3 class="icon-head">'.$this->__('Cron jobs').'</h3></td><td class="form-buttons">'.$block->toHtml().'</td></tr></tbody></table></div>';
		$html .= '<div class="eprev">'.Mage::getSingleton('cronlog/report')->send(null, true).'</div>';

		$this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('core/text')->setText($html));
		$this->renderLayout();
	}

	public function viewAction() {

		$cron = Mage::getModel('cron/schedule')->load((int) $this->getRequest()->getParam('id', 0));

		if (empty($cron->getId())) {
			$this->_redirect('*/*/index');
		}
		else {
			Mage::register('current_job', $cron);
			$this->loadLayout()->renderLayout();
		}
	}

	public function newAction() {
		$this->loadLayout()->renderLayout();
		Mage::getSingleton('adminhtml/session')->unsFormData();
	}

	public function saveAction() {

		try {
			$code = $this->getRequest()->getPost('job_code');
			$cid  = 0;

			if (!empty($code) && !Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {

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
				$cid = $cron->getId();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Job number %d has been successfully scheduled.', $cid));
			}
		}
		catch (Throwable $t) {
			Mage::getSingleton('adminhtml/session')->addError($t->getMessage())->setFormData($this->getRequest()->getPost());
		}

		if (empty($this->getRequest()->getParam('back')))
			$this->_redirect('*/*/view', ['id' => $cid]);
		else
			$this->_redirect('*/*/new', ['code' => $code]);
	}

	public function runAction() {

		try {
			$cron = Mage::getModel('cron/schedule')->load((int) $this->getRequest()->getParam('id', 0));
			$cid  = $cron->getId();

			if (($cron->getData('status') == 'pending') && !Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {

				$dir = Mage::getBaseDir('log');
				if (!is_dir($dir))
					@mkdir($dir, 0755);

				exec(sprintf('command -v php%d.%d || command -v php', PHP_MAJOR_VERSION, PHP_MINOR_VERSION), $cmd);
				$cmd = trim(implode($cmd));
				if (empty($cmd))
					Mage::throwException('PHP not found');

				exec(sprintf(
					'%s %s %d %d >> %s 2>&1 &',
					escapeshellcmd($cmd),
					str_replace('Cronlog/etc', 'Cronlog/lib/run.php', Mage::getModuleDir('etc', 'Luigifab_Cronlog')),
					$cid,
					Mage::getIsDeveloperMode() ? 1 : 0,
					$dir.'/cron.log'
				));

				sleep(2);

				$cron->load($cid);
				if ($cron->getData('status') != 'pending')
					Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Job number %d has been successfully started.', $cid));
			}
		}
		catch (Throwable $t) {
			Mage::getSingleton('adminhtml/session')->addError($t->getMessage());
		}

		if (empty($cid))
			$this->_redirect('*/*/index');
		else
			$this->_redirect('*/*/view', ['id' => $cid]);
	}

	public function cancelAction() {

		try {
			$id = (int) $this->getRequest()->getParam('id', 0);
			if (!empty($id) && !Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {
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
			$id = (int) $this->getRequest()->getParam('id', 0);
			if (!empty($id) && !Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {
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