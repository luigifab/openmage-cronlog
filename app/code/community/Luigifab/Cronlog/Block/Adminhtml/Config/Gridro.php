<?php
/**
 * Created S/31/05/2014
 * Updated S/11/11/2017
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

class Luigifab_Cronlog_Block_Adminhtml_Config_Gridro extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {

		parent::__construct();

		$this->setId('cronlog_grid_ro');

		$this->setUseAjax(true);
		$this->setSaveParametersInSession(false);
		$this->setPagerVisibility(false);
		$this->setFilterVisibility(false);
	}

	protected function _prepareCollection() {
		$this->setCollection(Mage::getModel('cronlog/source_jobs')->getCollection('ro'));
		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {

		$this->addColumn('module', array(
			'header'    => $this->__('Module name'),
			'index'     => 'module',
			'filter'    => false,
			'sortable'  => false
		));

		$this->addColumn('job_code', array(
			'header'    => $this->__('Read-only job'),
			'index'     => 'job_code',
			'width'     => '30%',
			'filter'    => false,
			'sortable'  => false
		));

		$this->addColumn('cron_expr', array(
			'header'    => $this->__('Configuration'),
			'index'     => 'cron_expr',
			'filter'    => false,
			'sortable'  => false
		));

		$this->addColumn('model', array(
			'header'    => 'Model',
			'index'     => 'model',
			'width'     => '30%',
			'filter'    => false,
			'sortable'  => false
		));

		$this->addColumn('status', array(
			'header'    => $this->__('Status'),
			'index'     => 'status',
			'type'      => 'options',
			'options'   => array(
				'enabled'  => $this->helper('cronlog')->_('Enabled'),
				'disabled' => $this->helper('cronlog')->_('Disabled')
			),
			'align'     => 'status',
			'width'     => '120px',
			'filter'    => false,
			'sortable'  => false,
			'frame_callback' => array($this, 'decorateStatus')
		));

		$this->addColumn('action', array(
			'type'      => 'action',
			'align'     => 'center',
			'width'     => '85px',
			'filter'    => false,
			'sortable'  => false,
			'is_system' => true,
			'frame_callback' => array($this, 'decorateLink')
		));

		return parent::_prepareColumns();
	}


	public function getRowClass($row) {
		return ($row->getData('status') == 'disabled') ? 'readonly disabled' : 'readonly';
	}

	public function getRowUrl($row) {
		return false;
	}

	public function canDisplayContainer() {
		return false;
	}

	public function getMessagesBlock() {
		return Mage::getBlockSingleton('core/template');
	}

	public function decorateStatus($value, $row, $column, $isExport) {
		return sprintf('<span class="grid-%s">%s</span>', $row->getData('status'), $value);
	}

	public function decorateLink($value, $row, $column, $isExport) {

		$url = $this->getUrl('*/*/save', array('code' => $row->getData('job_code')));
		$txt = $this->__(($row->getData('status') == 'disabled') ? 'Enable' : 'Disable');

		return (!$row->getData('is_read_only')) ? sprintf('<a href="%s" onclick="return cronlog.action(this.href);">%s</a>', $url, $txt) : '';
	}
}