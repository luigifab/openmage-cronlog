<?php
/**
 * Created W/29/02/2012
 * Updated J/07/12/2017
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

class Luigifab_Cronlog_Block_Adminhtml_History_View extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {

		parent::__construct();

		$object = Mage::registry('current_job');
		$params = array('id' => $object->getId());

		$this->_controller = 'adminhtml_history';
		$this->_blockGroup = 'cronlog';
		$this->_headerText = $this->__('Cron job number %d - %s', $object->getId(), $object->getData('job_code'));

		$this->_removeButton('add');

		$this->_addButton('back', array(
			'label'   => $this->__('Back'),
			'onclick' => "setLocation('".$this->getUrl('*/*/index')."');",
			'class'   => 'back'
		));

		$this->_addButton('delete', array(
			'label'   => $this->__('Delete'),
			'onclick' => "deleteConfirm('".addslashes($this->__('Are you sure?'))."', '".$this->getUrl('*/*/delete', $params)."');",
			'class'   => 'delete'
		));
		$this->_addButton('action', array(
			'label'   => $this->__('Restart the job'),
			'onclick' => "setLocation('".$this->getUrl('*/*/new', array('id' => $object->getId(), 'code' => $object->getData('job_code')))."');",
			'class'   => 'add'
		));
	}

	public function getGridHtml() {

		$help   = $this->helper('cronlog');
		$object = Mage::registry('current_job');

		// status
		if (in_array($object->getData('status'), array('success', 'error')))
			$status = $this->helper('cronlog')->_(ucfirst($object->getData('status')));
		else
			$status = $this->__(ucfirst($object->getData('status')));

		// html
		$html = array();
		$html[] = '<div class="content">';
		$html[] = '<div>';
		$html[] = '<ul>';
		$html[] = '<li>'.$help->_('Created At: %s', $this->formatDate($object->getData('created_at'))).'</li>';

		if (!in_array($object->getData('finished_at'), array('', '0000-00-00 00:00:00', null))) {

			$html[] = '<li>'.$help->_('Scheduled At: %s', $this->formatDate($object->getData('scheduled_at'))).'</li>';
			$html[] = '<li><strong>'.$help->_('Executed At: %s', $this->formatDate($object->getData('executed_at'))).'</strong></li>';
			$html[] = '<li>'.$help->_('Finished At: %s', $this->formatDate($object->getData('finished_at'))).'</li>';

			$duration = $help->getHumanDuration($object);
			if (!empty($duration))
				$html[] = '<li>'.$this->__('Duration: %s', $duration).'</li>';
		}
		else if (!in_array($object->getData('executed_at'), array('', '0000-00-00 00:00:00', null))) {
			$html[] = '<li>'.$help->_('Scheduled At: %s', $this->formatDate($object->getData('scheduled_at'))).'</li>';
			$html[] = '<li><strong>'.$help->_('Executed At: %s', $this->formatDate($object->getData('executed_at'))).'</strong></li>';
		}
		else {
			$html[] = '<li><strong>'.$help->_('Scheduled At: %s', $this->formatDate($object->getData('scheduled_at'))).'</strong></li>';
		}

		$html[] = '</ul>';
		$html[] = '<ul>';
		$html[] = '<li><strong class="status-'.$object->getData('status').'">'.$this->__('Status: <span>%s</span>', $status).'</strong></li>';
		$html[] = '<li>'.$this->__('Code: %s', $object->getData('job_code')).'</li>';
		$html[] = '</ul>';
		$html[] = '</div>';
		$html[] = '<pre lang="mul">'.$object->getMessages().'</pre>';
		$html[] = '</div>';

		return implode("\n", $html);
	}

	protected function _prepareLayout() {
		//return parent::_prepareLayout();
	}

	public function formatDate($date = null, $format = Zend_Date::DATETIME_LONG, $showTime = false) {
		$object = Mage::getSingleton('core/locale');
		return str_replace($object->date($date)->toString(Zend_Date::TIMEZONE), '', $object->date($date)->toString($format));
	}
}