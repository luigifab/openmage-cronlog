<?php
/**
 * Created W/29/02/2012
 * Updated D/31/08/2014
 * Version 19
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

		$job = Mage::registry('current_job');
		$text = $this->helper('core')->__('Are you sure?');

		parent::__construct();

		$this->_controller = 'adminhtml_show';
		$this->_blockGroup = 'cronlog';
		$this->_headerText = $this->__('Cron job number %d', $job->getId());

		$this->_removeButton('add');

		$this->_addButton('back', array(
			'label'   => $this->helper('adminhtml')->__('Back'),
			'onclick' => "setLocation('".$this->getUrl('*/*/index')."');",
			'class'   => 'back'
		));

		$this->_addButton('action', array(
			'label'   => ($job->getStatus() === 'pending') ? $this->helper('adminhtml')->__('Cancel') : $this->__('Restart task'),
			'onclick' => ($job->getStatus() === 'pending') ?
				"deleteConfirm('".addslashes($text)."', '".$this->getUrl('*/*/cancel', array('id' => $job->getId()))."');" :
				"setLocation('".$this->getUrl('*/*/new', array('id' => $job->getId(), 'code' => $job->getJobCode()))."');",
			'class'   => ($job->getStatus() === 'pending') ? 'delete' : 'add'
		));
	}

	public function getGridHtml() {

		$job = Mage::registry('current_job');
		$date = Mage::getSingleton('core/locale'); //date($date, $format, $locale = null, $useTimezone = null)

		$html  = '<div class="content">';
		$html .= "\n".'<ul>';
		$html .= "\n".'<li>'.$this->__('Created At: %s', $date->date($job->getCreatedAt(), Zend_Date::ISO_8601)).'</li>';

		if (!in_array($job->getExecutedAt(), array('', '0000-00-00 00:00:00', null))) {
			$html .= "\n".'<li>'.$this->__('Scheduled At: %s', $date->date($job->getScheduledAt(), Zend_Date::ISO_8601)).'</li>';
			$html .= "\n".'<li><strong>'.$this->__('Executed At: %s', $date->date($job->getExecutedAt(), Zend_Date::ISO_8601)).'</strong></li>';
		}
		else {
			$html .= "\n".'<li><strong>'.$this->__('Scheduled At: %s', $date->date($job->getScheduledAt(), Zend_Date::ISO_8601)).'</strong></li>';
		}
		if (!in_array($job->getFinishedAt(), array('', '0000-00-00 00:00:00', null))) {
			$html .= "\n".'<li>'.$this->__('Finished At: %s', $date->date($job->getFinishedAt(), Zend_Date::ISO_8601)).'</li>';
			$duration = Mage::getBlockSingleton('cronlog/adminhtml_widget_duration')->render($job);
			if (strlen($duration) > 0)
				$html .= "\n".'<li>'.$this->__('Duration: %s', $duration).'</li>';
		}

		$html .= "\n".'</ul>';
		$html .= "\n".'<p class="status-'.$job->getStatus().'"><strong>'.$this->__('Status: %s (%s)', $this->__(ucfirst($job->getStatus())), $job->getStatus()).'</strong><br />'.$this->__('Code: %s', $job->getJobCode()).'</p>';
		$html .= "\n".((strlen($job->getMessages()) > 0) ? '<pre>'.$job->getMessages().'</pre>' : '<pre></pre>');
		$html .= "\n".'</div>';

		return $html;
	}

	protected function _prepareLayout() {
		//return parent::_prepareLayout();
	}
}