<?php
/**
 * Created W/29/02/2012
 * Updated J/07/05/2015
 * Version 28
 *
 * Copyright 2012-2015 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

		$job = Mage::registry('current_job');
		$this->_controller = 'adminhtml_history';
		$this->_blockGroup = 'cronlog';
		$this->_headerText = $this->__('Cron job number %d - %s', $job->getId(), $job->getJobCode());

		$this->_removeButton('add');

		$this->_addButton('back', array(
			'label'   => $this->helper('adminhtml')->__('Back'),
			'onclick' => "setLocation('".$this->getUrl('*/*/index')."');",
			'class'   => 'back'
		));

		if ($job->getStatus() === 'pending') {
			$this->_addButton('delete', array(
				'label'   => $this->helper('adminhtml')->__('Cancel'),
				'onclick' => "deleteConfirm('".addslashes($this->helper('core')->__('Are you sure?'))."', '".$this->getUrl('*/*/cancel', array('id' => $job->getId()))."');",
				'class'   => 'delete'
			));
		}
		else {
			$this->_addButton('delete', array(
				'label'   => $this->helper('adminhtml')->__('Delete'),
				'onclick' => "deleteConfirm('".addslashes($this->helper('core')->__('Are you sure?'))."', '".$this->getUrl('*/*/delete', array('id' => $job->getId()))."');",
				'class'   => 'delete'
			));
			$this->_addButton('action', array(
				'label'   => $this->__('Restart job'),
				'onclick' => "setLocation('".$this->getUrl('*/*/new', array('id' => $job->getId(), 'code' => $job->getJobCode()))."');",
				'class'   => 'add'
			));
		}
	}

	public function getGridHtml() {

		$that = $this->helper('cronlog');
		$job  = Mage::registry('current_job');
		$date = Mage::getSingleton('core/locale'); //date($date, $format, $locale = null, $useTimezone = null

		$status = trim(str_replace('(0)', '', $this->__(ucfirst($job->getStatus().' (%d)'), 0)));

		$html  = '<div class="content">';
		$html .= "\n".'<ul>';
		$html .= "\n".'<li>'.$that->_('Created At: %s', $date->date($job->getCreatedAt(), Zend_Date::ISO_8601)).'</li>';

		if (!in_array($job->getFinishedAt(), array('', '0000-00-00 00:00:00', null))) {
			$html .= "\n".'<li>'.$that->_('Scheduled At: %s', $date->date($job->getScheduledAt(), Zend_Date::ISO_8601)).'</li>';
			$html .= "\n".'<li><strong>'.$that->_('Executed At: %s', $date->date($job->getExecutedAt(), Zend_Date::ISO_8601)).'</strong></li>';
			$html .= "\n".'<li>'.$that->_('Finished At: %s', $date->date($job->getFinishedAt(), Zend_Date::ISO_8601)).'</li>';
			$duration = Mage::getBlockSingleton('cronlog/adminhtml_history_grid')->decorateDuration(null, $job, null, false);
			if (strlen($duration) > 0)
				$html .= "\n".'<li>'.$this->__('Duration: %s', $duration).'</li>';
		}
		else if (!in_array($job->getExecutedAt(), array('', '0000-00-00 00:00:00', null))) {
			$html .= "\n".'<li>'.$that->_('Scheduled At: %s', $date->date($job->getScheduledAt(), Zend_Date::ISO_8601)).'</li>';
			$html .= "\n".'<li><strong>'.$that->_('Executed At: %s', $date->date($job->getExecutedAt(), Zend_Date::ISO_8601)).'</strong></li>';
		}
		else {
			$html .= "\n".'<li><strong>'.$that->_('Scheduled At: %s', $date->date($job->getScheduledAt(), Zend_Date::ISO_8601)).'</strong></li>';
		}

		$html .= "\n".'</ul>';
		$html .= "\n".'<ul>';
		$html .= "\n".'<li><strong class="status-'.$job->getStatus().'">'.$this->__('Status: %s (%s)', $status, $job->getStatus()).'</strong></li>';
		$html .= "\n".'<li>'.$this->__('Code: %s', $job->getJobCode()).'</li>';
		$html .= "\n".'</ul>';
		$html .= "\n".'<pre>'.$job->getMessages().'</pre>';
		$html .= "\n".'</div>';

		return $html;
	}

	protected function _prepareLayout() {
		//return parent::_prepareLayout();
	}
}