<?php
/**
 * Created W/29/02/2012
 * Updated V/15/10/2021
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

class Luigifab_Cronlog_Block_Adminhtml_History_View extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {

		parent::__construct();

		$cron    = Mage::registry('current_job');
		$params  = ['id' => $cron->getId()];
		$confirm = $this->helper('cronlog')->escapeEntities($this->__('Are you sure?'), true);

		$this->_controller = 'adminhtml_history';
		$this->_blockGroup = 'cronlog';
		$this->_headerText = $this->__('Cron job number %d - %s', $cron->getId(), $cron->getData('job_code'));

		$this->_removeButton('add');

		$this->_addButton('back', [
			'label'   => $this->__('Back'),
			'onclick' => "setLocation('".$this->getUrl('*/*/index')."');",
			'class'   => 'back'
		]);

		$this->_addButton('delete', [
			'label'   => $this->__('Delete'),
			'onclick' => "deleteConfirm('".$confirm."', '".$this->getUrl('*/*/delete', $params)."');",
			'class'   => 'delete'
		]);

		$this->_addButton('restart', [
			'label'   => $this->__('Restart the job'),
			'onclick' => "setLocation('".$this->getUrl('*/*/new', ['id' => $cron->getId(), 'code' => $cron->getData('job_code')])."');",
			'class'   => 'add'
		]);

		if ($cron->getData('status') == 'pending') {
			$this->_addButton('run', [
				'label'   => $this->__('Run the job'),
				'onclick' => "confirmSetLocation('".$confirm."', '".$this->getUrl('*/*/run', ['id' => $cron->getId()])."');",
				'class'   => 'save'
			]);
		}
	}

	public function getGridHtml() {

		$cron  = Mage::registry('current_job');
		$class = 'class="cronlog-status grid-'.$cron->getData('status').'"';
		$help  = $this->helper('cronlog');

		// status
		if (in_array($cron->getData('status'), ['success', 'error']))
			$status = $this->helper('cronlog')->_(ucfirst($cron->getData('status')));
		else
			$status = $this->__(ucfirst($cron->getData('status')));

		// html
		$html   = [];
		$html[] = '<div class="content">';
		$html[] = '<div>';
		$html[] = '<ul>';
		$html[] = '<li>'.$help->_('Created At: %s', $help->formatDate($cron->getData('created_at'))).'</li>';

		if (!in_array($cron->getData('finished_at'), ['', '0000-00-00 00:00:00', null])) {

			$html[] = '<li>'.$help->_('Scheduled At: %s', $help->formatDate($cron->getData('scheduled_at'))).'</li>';
			$html[] = '<li><strong>'.$help->_('Executed At: %s', $help->formatDate($cron->getData('executed_at'))).'</strong></li>';
			$html[] = '<li>'.$help->_('Finished At: %s', $help->formatDate($cron->getData('finished_at'))).'</li>';

			$duration = $help->getHumanDuration($cron->getData('executed_at'), $cron->getData('finished_at'));
			if (!empty($duration))
				$html[] = '<li>'.$this->__('Duration: %s', $duration).'</li>';
		}
		else if (in_array($cron->getData('executed_at'), ['', '0000-00-00 00:00:00', null])) {
			$html[] = '<li><strong>'.$help->_('Scheduled At: %s', $help->formatDate($cron->getData('scheduled_at'))).'</strong></li>';
		}
		else {
			$html[] = '<li>'.$help->_('Scheduled At: %s', $help->formatDate($cron->getData('scheduled_at'))).'</li>';
			$html[] = '<li><strong>'.$help->_('Executed At: %s', $help->formatDate($cron->getData('executed_at'))).'</strong></li>';
		}

		$html[] = '</ul>';
		$html[] = '<ul>';
		$html[] = '<li><strong>'.$this->__('Status: <span %s>%s</span>', $class, $status).'</strong></li>';
		$html[] = '<li>'.$this->__('Code: %s', $cron->getData('job_code')).'</li>';
		$html[] = '</ul>';
		$html[] = '</div>';
		$html[] = '<pre lang="mul">'.$help->escapeEntities($cron->getData('messages')).'</pre>';
		$html[] = '</div>';

		return implode("\n", $html);
	}

	protected function _prepareLayout() {
		//return parent::_prepareLayout();
	}
}