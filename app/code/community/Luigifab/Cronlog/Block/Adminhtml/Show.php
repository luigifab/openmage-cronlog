<?php
/**
 * Created W/29/02/2012
 * Updated J/01/03/2012
 * Version 3
 *
 * Copyright 2012 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Cronlog_Block_Adminhtml_Show extends Mage_Adminhtml_Block_Widget_View_Container {

	public function __construct() {

		parent::__construct();

		$this->_controller = 'adminhtml_show';
		$this->_blockGroup = 'cronlog';

		$this->_headerText = $this->__('Cron job number %d', $this->getRequest()->getParam('id'));
		$this->_removeButton('delete');
		$this->_removeButton('edit');
	}

	public function getViewHtml() {

		$date = Mage::getSingleton('core/locale');
		$cron = Mage::getModel('cron/schedule')->load($this->getRequest()->getParam('id'));

		$html  = '<div class="content">';
		$html .= '<ul>';
		$html .= '<li>'.$this->__('Created At: %s', $date->date($cron->getCreatedAt(), Zend_Date::ISO_8601, null, false)).'</li>';

		if (strlen($cron->getExecutedAt()) > 0) {
			$html .= '<li>'.$this->__('Scheduled At: %s', $date->date($cron->getScheduledAt(), Zend_Date::ISO_8601, null, false)).'</li>';
			$html .= '<li><strong>'.$this->__('Executed At: %s', $date->date($cron->getExecutedAt(), Zend_Date::ISO_8601, null, false)).'</strong></li>';
		}
		else {
			$html .= '<li><strong>'.$this->__('Scheduled At: %s', $date->date($cron->getScheduledAt(), Zend_Date::ISO_8601, null, false)).'</strong></li>';
		}

		if (strlen($cron->getFinishedAt()) > 0)
			$html .= '<li>'.$this->__('Finished At: %s', $date->date($cron->getFinishedAt(), Zend_Date::ISO_8601, null, false)).'</li>';

		$html .= '</ul>';

		if (in_array($cron->getStatus(), array('missed', 'error'))) {
			$html .= '<p class="status ee"><strong>'.$this->__('Status: %s (%s)', $this->__(ucfirst($cron->getStatus())), $cron->getStatus()).'</strong>';
			$html .= '<br />'.$this->__('Code: %s', $cron->getJobCode()).'</p>';
		}
		else {
			$html .= '<p class="status"><strong>'.$this->__('Status: %s (%s)', $this->__(ucfirst($cron->getStatus())), $cron->getStatus()).'</strong>';
			$html .= '<br />'.$this->__('Code: %s', $cron->getJobCode()).'</p>';
		}

		$html .= (strlen($cron->getMessages()) > 0) ? '<pre>'.$cron->getMessages().'</pre>' : '<pre>'.$this->__('No message.').'</pre>';
		$html .= '</div>';

		return $html;
	}

	protected function _prepareLayout() {
		//return parent::_prepareLayout();
	}
}