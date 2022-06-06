<?php
/**
 * Created W/29/02/2012
 * Updated V/20/05/2022
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

class Luigifab_Cronlog_Block_Adminhtml_History_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {

		parent::__construct();

		$this->setId('cronlog_grid');
		$this->setDefaultSort('schedule_id');
		$this->setDefaultDir('desc');

		$this->setUseAjax(true);
		$this->setSaveParametersInSession(true);
		$this->setPagerVisibility(true);
		$this->setFilterVisibility(true);
		$this->setDefaultLimit(max($this->_defaultLimit, (int) Mage::getStoreConfig('cronlog/general/number')));
	}

	protected function _prepareCollection() {
		$this->setCollection(Mage::getResourceModel('cron/schedule_collection'));
		return parent::_prepareCollection();
	}

	protected function _addColumnFilterToCollection($column) {

		if (($column->getId() == 'job_code') && !empty($words = $column->getFilter()->getData('search_messages'))) {
			$words = array_filter(explode(' ', $words));
			foreach ($words as $word)
				$this->getCollection()->addFieldToFilter('messages', ['like' => '%'.$word.'%']);
		}

		return parent::_addColumnFilterToCollection($column);
	}

	protected function _prepareColumns() {

		$this->addColumn('schedule_id', [
			'header'    => $this->__('Id'),
			'index'     => 'schedule_id',
			'align'     => 'center',
			'width'     => '80px',
		]);

		$this->addColumn('job_code', [
			'header'    => $this->__('Job'),
			'index'     => 'job_code',
			'align'     => 'center',
			'filter'    => 'cronlog/adminhtml_history_filter',
			'frame_callback' => [$this, 'decorateCode'],
		]);

		$this->addColumn('created_at', [
			'header'    => $this->helper('cronlog')->_('Created At'),
			'index'     => 'created_at',
			'type'      => 'datetime',
			'format'    => Mage::getSingleton('core/locale')->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
			'align'     => 'center',
			'width'     => '150px',
			'frame_callback' => [$this, 'decorateDate'],
		]);

		$this->addColumn('scheduled_at', [
			'header'    => $this->helper('cronlog')->_('Scheduled At'),
			'index'     => 'scheduled_at',
			'type'      => 'datetime',
			'format'    => Mage::getSingleton('core/locale')->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
			'align'     => 'center',
			'width'     => '150px',
			'frame_callback' => [$this, 'decorateDate'],
		]);

		$this->addColumn('executed_at', [
			'header'    => $this->helper('cronlog')->_('Executed At'),
			'index'     => 'executed_at',
			'type'      => 'datetime',
			'format'    => Mage::getSingleton('core/locale')->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
			'align'     => 'center',
			'width'     => '150px',
			'frame_callback' => [$this, 'decorateDate'],
		]);

		$this->addColumn('finished_at', [
			'header'    => $this->helper('cronlog')->_('Finished At'),
			'index'     => 'finished_at',
			'type'      => 'datetime',
			'format'    => Mage::getSingleton('core/locale')->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
			'align'     => 'center',
			'width'     => '150px',
			'frame_callback' => [$this, 'decorateDate'],
		]);

		$this->addColumn('duration', [
			'header'    => $this->__('Duration'),
			'index'     => 'duration',
			'align'     => 'center',
			'width'     => '60px',
			'filter'    => false,
			'sortable'  => false,
			'frame_callback' => [$this, 'decorateDuration'],
		]);

		$this->addColumn('status', [
			'header'    => $this->__('Status'),
			'index'     => 'status',
			'type'      => 'options',
			'options'   => [
				'pending' => $this->__('Pending'),
				'running' => $this->__('Running'),
				'error'   => $this->helper('cronlog')->_('Error'),
				'missed'  => $this->__('Missed'),
				'success' => $this->helper('cronlog')->_('Success'),
			],
			'width'     => '125px',
			'frame_callback' => [$this, 'decorateStatus'],
		]);

		$this->addColumn('action', [
			'type'      => 'action',
			'getter'    => 'getId',
			'actions'   => [
				[
					'caption' => $this->__('View'),
					'url'     => ['base' => '*/*/view'],
					'field'   => 'id',
				],
			],
			'align'     => 'center',
			'width'     => '55px',
			'filter'    => false,
			'sortable'  => false,
			'is_system' => true,
		]);

		// recherche des codes
		$database = Mage::getSingleton('core/resource');
		$reader   = $database->getConnection('core_read');

		$codes = $reader->fetchAssoc($reader->select()->distinct()->from($database->getTableName('cron_schedule'), 'job_code'));
		$codes = array_keys($codes);
		$codes = array_combine($codes, $codes);

		ksort($codes);
		$this->getColumn('job_code')->setData('options', $codes);

		// mode texte et mode liste déroulante, 2 en 1
		$filter = $this->getParam($this->getVarNameFilter(), null);
		if (is_string($filter) || !empty($this->_defaultFilter))
			$filter = array_merge($this->_defaultFilter, $this->helper('adminhtml')->prepareFilterString($filter));

		$this->getColumn('job_code')->getFilter()
			->setData('current_filter',  empty($filter['job_code']) ? null : $filter['job_code'])
			->setData('current_in_list', empty($filter['job_code']) ? false : in_array($filter['job_code'], $codes))
			->setData('search_messages', empty($filter['job_code_msg']) ? false : $filter['job_code_msg']);

		return parent::_prepareColumns();
	}


	public function getRowClass($row) {
		return '';
	}

	public function getRowUrl($row) {
		return $this->getUrl('*/*/view', ['id' => $row->getId()]);
	}


	public function decorateStatus($value, $row, $column, $isExport) {
		return $isExport ? $value : sprintf('<span class="cronlog-status grid-%s">%s</span>', $row->getData('status'), $value);
	}

	public function decorateDuration($value, $row, $column, $isExport) {
		return $this->helper('cronlog')->getHumanDuration($row->getData('executed_at'), $row->getData('finished_at'));
	}

	public function decorateDate($value, $row, $column, $isExport) {
		return in_array($row->getData($column->getIndex()), ['', '0000-00-00 00:00:00', null]) ? '' : $value;
	}

	public function decorateCode($value, $row, $column, $isExport) {
		return $row->getData('job_code');
	}
}