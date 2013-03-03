<?php
/**
 * Created W/29/02/2012
 * Updated D/03/03/2013
 * Version 9
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

class Luigifab_Cronlog_Block_Adminhtml_View extends Mage_Adminhtml_Block_Widget_View_Container {

	public function __construct() {

		parent::__construct();
		$job = Mage::registry('current_job');

		$this->_controller = 'adminhtml_show';
		$this->_blockGroup = 'cronlog';
		$this->_removeButton('edit');

		if ($job->getStatus() === 'pending') {
			$this->_addButton('delete', array(
				'label'   => $this->helper('adminhtml')->__('Cancel'),
				'onclick' => "deleteConfirm('".addslashes($this->helper('core')->__('Are you sure?'))."','".$this->getUrl('*/*/cancel', array('id' => $this->getRequest()->getParam('id')))."')",
				'class'   => 'delete'
			));
		}

		$this->_headerText = $this->__('Cron job number %d', $this->getRequest()->getParam('id'));
	}

	public function getViewHtml() {

		$job = Mage::registry('current_job');
		$date = Mage::getSingleton('core/locale');

		$html  = '<div class="content">';
		$html .= "\n".'<ul>';
		$html .= "\n".'<li>'.$this->__('Created At: %s', $date->date($job->getCreatedAt(), Zend_Date::ISO_8601, null, true)).'</li>';

		if ((strlen($job->getExecutedAt()) > 0) && ($job->getExecutedAt() != '0000-00-00 00:00:00')) {
			$html .= "\n".'<li>'.$this->__('Scheduled At: %s', $date->date($job->getScheduledAt(), Zend_Date::ISO_8601, null, true)).'</li>';
			$html .= "\n".'<li><strong>'.$this->__('Executed At: %s', $date->date($job->getExecutedAt(), Zend_Date::ISO_8601, null, true)).'</strong></li>';
		}
		else {
			$html .= "\n".'<li><strong>'.$this->__('Scheduled At: %s', $date->date($job->getScheduledAt(), Zend_Date::ISO_8601, null, true)).'</strong></li>';
		}

		if ((strlen($job->getFinishedAt()) > 0) && ($job->getFinishedAt() != '0000-00-00 00:00:00')) {
			$html .= "\n".'<li>'.$this->__('Finished At: %s', $date->date($job->getFinishedAt(), Zend_Date::ISO_8601, null, true)).'</li>';
		}

		$html .= "\n".'</ul>';

		if (in_array($job->getStatus(), array('missed', 'error'))) {
			$html .= "\n".'<p class="status error"><strong>'.$this->__('Status: %s (%s)', $this->__(ucfirst($job->getStatus())), $job->getStatus()).'</strong>';
			$html .= "\n".'<br />'.$this->__('Code: %s', $job->getJobCode()).'</p>';
		}
		else if ($job->getStatus() === 'running') {
			$html .= "\n".'<p class="status run"><strong>'.$this->__('Status: %s (%s)', $this->__(ucfirst($job->getStatus())), $job->getStatus()).'</strong>';
			$html .= "\n".'<br />'.$this->__('Code: %s', $job->getJobCode()).'</p>';
		}
		else {
			$html .= "\n".'<p class="status"><strong>'.$this->__('Status: %s (%s)', $this->__(ucfirst($job->getStatus())), $job->getStatus()).'</strong>';
			$html .= "\n".'<br />'.$this->__('Code: %s', $job->getJobCode()).'</p>';
		}

		$html .= "\n".((strlen($job->getMessages()) > 0) ? '<pre>'.$job->getMessages().'</pre>' : '<pre>'.$this->__('No message.')).'</pre>';
		$html .= "\n".'</div>';

		return $html;
	}

	protected function _prepareLayout() {
		//return parent::_prepareLayout();
	}
}