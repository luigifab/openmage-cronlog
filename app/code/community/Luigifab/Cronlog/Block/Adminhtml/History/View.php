<?php
/**
 * Created W/29/02/2012
 * Updated S/09/11/2019
 *
 * Copyright 2012-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/magento/cronlog
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
		$params = ['id' => $job->getId()];

		$this->_controller = 'adminhtml_history';
		$this->_blockGroup = 'cronlog';
		$this->_headerText = $this->__('Cron job number %d - %s', $job->getId(), $job->getData('job_code'));

		$this->_removeButton('add');

		$this->_addButton('back', [
			'label'   => $this->__('Back'),
			'onclick' => "setLocation('".$this->getUrl('*/*/index')."');",
			'class'   => 'back'
		]);

		$this->_addButton('delete', [
			'label'   => $this->__('Delete'),
			'onclick' => "deleteConfirm('".addslashes($this->__('Are you sure?'))."', '".$this->getUrl('*/*/delete', $params)."');",
			'class'   => 'delete'
		]);

		$this->_addButton('restart', [
			'label'   => $this->__('Restart the job'),
			'onclick' => "setLocation('".$this->getUrl('*/*/new', ['id' => $job->getId(), 'code' => $job->getData('job_code')])."');",
			'class'   => 'add'
		]);
	}

	public function getGridHtml() {

		$job = Mage::registry('current_job');
		$class  = 'class="cronlog-status grid-'.$job->getData('status').'"';
		$help   = $this->helper('cronlog');

		// status
		if (in_array($job->getData('status'), ['success', 'error']))
			$status = $this->helper('cronlog')->_(ucfirst($job->getData('status')));
		else
			$status = $this->__(ucfirst($job->getData('status')));

		// html
		$html   = [];
		$html[] = '<div class="content">';
		$html[] = '<div>';
		$html[] = '<ul>';
		$html[] = '<li>'.$help->_('Created At: %s', $help->formatDate($job->getData('created_at'))).'</li>';

		if (!in_array($job->getData('finished_at'), ['', '0000-00-00 00:00:00', null])) {

			$html[] = '<li>'.$help->_('Scheduled At: %s', $help->formatDate($job->getData('scheduled_at'))).'</li>';
			$html[] = '<li><strong>'.$help->_('Executed At: %s', $help->formatDate($job->getData('executed_at'))).'</strong></li>';
			$html[] = '<li>'.$help->_('Finished At: %s', $help->formatDate($job->getData('finished_at'))).'</li>';

			$duration = $help->getHumanDuration($job->getData('executed_at'), $job->getData('finished_at'));
			if (!empty($duration))
				$html[] = '<li>'.$this->__('Duration: %s', $duration).'</li>';
		}
		else if (in_array($job->getData('executed_at'), ['', '0000-00-00 00:00:00', null])) {
			$html[] = '<li><strong>'.$help->_('Scheduled At: %s', $help->formatDate($job->getData('scheduled_at'))).'</strong></li>';
		}
		else {
			$html[] = '<li>'.$help->_('Scheduled At: %s', $help->formatDate($job->getData('scheduled_at'))).'</li>';
			$html[] = '<li><strong>'.$help->_('Executed At: %s', $help->formatDate($job->getData('executed_at'))).'</strong></li>';
		}

		$html[] = '</ul>';
		$html[] = '<ul>';
		$html[] = '<li><strong>'.$this->__('Status: <span %s>%s</span>', $class, $status).'</strong></li>';
		$html[] = '<li>'.$this->__('Code: %s', $job->getData('job_code')).'</li>';
		$html[] = '</ul>';
		$html[] = '</div>';
		$html[] = '<pre lang="mul">'.$help->escapeEntities($job->getMessages()).'</pre>';
		$html[] = '</div>';

		return implode("\n", $html);
	}

	protected function _prepareLayout() {
		//return parent::_prepareLayout();
	}
}