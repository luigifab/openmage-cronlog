<?php
/**
 * Created S/31/05/2014
 * Updated D/26/06/2022
 *
 * Copyright 2012-2023 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Cronlog_Block_Adminhtml_Manage_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {

		parent::__construct();

		$this->setId('cronlog_grid_rw');

		$this->setUseAjax(true);
		$this->setSaveParametersInSession(false);
		$this->setPagerVisibility(false);
		$this->setFilterVisibility(false);
	}

	protected function _prepareCollection() {
		$this->setCollection(Mage::getModel('cronlog/source_jobs')->getCollection('rw'));
		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {

		$this->addColumn('module', [
			'header'    => $this->__('Module name'),
			'index'     => 'module',
			'filter'    => false,
			'sortable'  => false,
		]);

		$this->addColumn('job_code', [
			'header'    => $this->__('Job'),
			'index'     => 'job_code',
			'filter'    => false,
			'sortable'  => false,
		]);

		$this->addColumn('cron_expr', [
			'header'    => $this->__('Configuration'),
			'index'     => 'cron_expr',
			'filter'    => false,
			'sortable'  => false,
		]);

		$this->addColumn('model', [
			'header'    => 'Model',
			'index'     => 'model',
			'filter'    => false,
			'sortable'  => false,
			'frame_callback' => [$this, 'decorateModel'],
		]);

		$this->addColumn('status', [
			'header'    => $this->__('Status'),
			'index'     => 'status',
			'type'      => 'options',
			'options'   => [
				'enabled'  => $this->helper('cronlog')->_('Enabled'),
				'disabled' => $this->helper('cronlog')->_('Disabled'),
			],
			'width'     => '120px',
			'filter'    => false,
			'sortable'  => false,
			'frame_callback' => [$this, 'decorateStatus'],
		]);

		$this->addColumn('action', [
			'type'      => 'action',
			'align'     => 'center',
			'width'     => '85px',
			'filter'    => false,
			'sortable'  => false,
			'is_system' => true,
			'frame_callback' => [$this, 'decorateLink'],
		]);

		return parent::_prepareColumns();
	}


	public function getRowClass($row) {
		return ($row->getData('status') == 'disabled') ? 'disabled' : '';
	}

	public function getRowUrl($row) {
		return false;
	}

	public function canDisplayContainer() {
		return false;
	}

	public function getMessagesBlock() {
		return Mage::getBlockSingleton('adminhtml/template');
	}


	public function decorateStatus($value, $row, $column, $isExport) {
		return $isExport ? $value : sprintf('<span class="cronlog-status grid-%s">%s</span>', $row->getData('status'), $value);
	}

	public function decorateModel($value, $row, $column, $isExport) {
		return $isExport ? $value : sprintf('%s <div>%s</div>', $value, str_replace('_Model_', '_<b>Model</b>_', $row->getData('class_name')));
	}

	public function decorateLink($value, $row, $column, $isExport) {
		return $row->getData('is_read_only') ? '' : sprintf('<button type="button" onclick="new Ajax.Updater(%s, \'%s\')">%s</button>',
			'document.getElementById(\'cronlog_grid_rw_table\').parentNode.parentNode.parentNode',
			$this->getUrl('*/*/save', ['code' => $row->getData('job_code')]),
			$this->__(($row->getData('status') == 'disabled') ? 'Enable' : 'Disable'));
	}
}