<?php
/**
 * Created W/29/02/2012
 * Updated W/09/11/2016
 *
 * Copyright 2012-2017 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Cronlog_Block_Adminhtml_History_View extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {

		parent::__construct();

		$job    = Mage::registry('current_job');
		$params = array('id' => $job->getId());

		$this->_controller = 'adminhtml_history';
		$this->_blockGroup = 'cronlog';
		$this->_headerText = $this->__('Cron job number %d - %s', $job->getId(), $job->getJobCode());

		$this->_removeButton('add');

		$this->_addButton('back', array(
			'label'   => $this->__('Back'),
			'onclick' => "setLocation('".$this->getUrl('*/*/index')."');",
			'class'   => 'back'
		));

		if ($job->getStatus() === 'pending') {
			$this->_addButton('delete', array(
				'label'   => $this->__('Cancel'),
				'onclick' => "deleteConfirm('".addslashes($this->__('Are you sure?'))."', '".$this->getUrl('*/*/cancel', $params)."');",
				'class'   => 'delete'
			));
		}
		else {
			$this->_addButton('delete', array(
				'label'   => $this->__('Delete'),
				'onclick' => "deleteConfirm('".addslashes($this->__('Are you sure?'))."', '".$this->getUrl('*/*/delete', $params)."');",
				'class'   => 'delete'
			));
			$this->_addButton('action', array(
				'label'   => $this->__('Restart the job'),
				'onclick' => "setLocation('".$this->getUrl('*/*/new', array('id' => $job->getId(), 'code' => $job->getJobCode()))."');",
				'class'   => 'add'
			));
		}
	}

	public function getGridHtml() {

		$help = $this->helper('cronlog');
		$job  = Mage::registry('current_job');
		$date = Mage::getSingleton('core/locale'); //date($date, $format, $locale = null, $useTimezone = null

		if (in_array($job->getStatus(), array('success', 'error')))
			$status = $this->helper('cronlog')->_(ucfirst($job->getStatus()));
		else
			$status = $this->__(ucfirst($job->getStatus()));

		$html = array();
		$html[] = '<div class="content">';
		$html[] = '<div>';
		$html[] = '<ul>';
		$html[] = '<li>'.$help->_('Created At: %s', $date->date($job->getCreatedAt(), Zend_Date::ISO_8601)).'</li>';

		if (!in_array($job->getFinishedAt(), array('', '0000-00-00 00:00:00', null))) {

			$html[] = '<li>'.$help->_('Scheduled At: %s', $date->date($job->getScheduledAt(), Zend_Date::ISO_8601)).'</li>';
			$html[] = '<li><strong>'.$help->_('Executed At: %s', $date->date($job->getExecutedAt(), Zend_Date::ISO_8601)).'</strong></li>';
			$html[] = '<li>'.$help->_('Finished At: %s', $date->date($job->getFinishedAt(), Zend_Date::ISO_8601)).'</li>';

			$duration = $help->getHumanDuration($job);
			if (strlen($duration) > 0)
				$html[] = '<li>'.$this->__('Duration: %s', $duration).'</li>';
		}
		else if (!in_array($job->getExecutedAt(), array('', '0000-00-00 00:00:00', null))) {
			$html[] = '<li>'.$help->_('Scheduled At: %s', $date->date($job->getScheduledAt(), Zend_Date::ISO_8601)).'</li>';
			$html[] = '<li><strong>'.$help->_('Executed At: %s', $date->date($job->getExecutedAt(), Zend_Date::ISO_8601)).'</strong></li>';
		}
		else {
			$html[] = '<li><strong>'.$help->_('Scheduled At: %s', $date->date($job->getScheduledAt(), Zend_Date::ISO_8601)).'</strong></li>';
		}

		$html[] = '</ul>';
		$html[] = '<ul>';
		$html[] = '<li><strong class="status-'.$job->getStatus().'">'.$this->__('Status: <span>%s</span>', $status).'</strong></li>';
		$html[] = '<li>'.$this->__('Code: %s', $job->getJobCode()).'</li>';
		$html[] = '</ul>';
		$html[] = '</div>';
		$html[] = '<pre>'.$job->getMessages().'</pre>';
		$html[] = '</div>';

		return implode("\n", $html);
	}

	protected function _prepareLayout() {
		//return parent::_prepareLayout();
	}
}