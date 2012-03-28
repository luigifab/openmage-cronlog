<?php
/**
 * Created W/29/02/2012
 * Updated W/28/03/2012
 * Version 6
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

class Luigifab_Cronlog_Block_Adminhtml_History_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {

		parent::__construct();

		$this->setId('cronloggrid');
		$this->setDefaultSort('schedule_id');
		$this->setDefaultDir('DESC');

		$this->setSaveParametersInSession(false);
		$this->setPagerVisibility(true);
		$this->setFilterVisibility(true);
		$this->setDefaultLimit(max($this->_defaultLimit, intval(Mage::getStoreConfig('cronlog/general/number'))));
	}

	protected function _prepareCollection() {

		$this->setCollection(Mage::getResourceModel('cron/schedule_collection'));

		if ((strlen(Mage::getStoreConfig('cronlog/general/filter')) > 0) && (strlen($this->getParam($this->getVarNameFilter())) < 10)) {
			$filter = $this->getParam($this->getVarNameFilter(), array('status' => Mage::getStoreConfig('cronlog/general/filter')));
			$this->_setFilterValues($filter);
		}

		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {

		$this->addColumn('schedule_id', array(
			'header'   => $this->helper('adminhtml')->__('Id'),
			'align'    => 'center',
			'width'    => '85px',
			'index'    => 'schedule_id',
			'sortable' => true
		));

		$this->addColumn('job_code', array(
			'header'   => $this->__('Job'),
			'align'    => 'center',
			'index'    => 'job_code',
			'sortable' => true
		));

		$this->addColumn('status', array(
			'header'   => $this->helper('adminhtml')->__('Status'),
			'align'    => 'center',
			'width'    => '100px',
			'index'    => 'status',
			'sortable' => true,
			'type'     => 'options',
			'renderer' => 'cronlog/adminhtml_widget_status',
			'options'  => array(
				'pending' => $this->__('Pending'),
				'running' => $this->__('Running'),
				'success' => $this->__('Success'),
				'missed'  => $this->__('Missed'),
				'error'   => $this->__('Error')
			)
		));

		$this->addColumn('created_at', array(
			'header'   => $this->__('Created At'),
			'width'    => '185px',
			'align'    => 'center',
			'type'     => 'datetime',
			'renderer' => 'cronlog/adminhtml_widget_datetime',
			'index'    => 'created_at',
			'sortable' => true,
		));

		$this->addColumn('scheduled_at', array(
			'header'   => $this->__('Scheduled At'),
			'width'    => '185px',
			'align'    => 'center',
			'type'     => 'datetime',
			'renderer' => 'cronlog/adminhtml_widget_datetime',
			'index'    => 'scheduled_at',
			'sortable' => true,
		));

		$this->addColumn('executed_at', array(
			'header'   => $this->__('Executed At'),
			'width'    => '185px',
			'align'    => 'center',
			'type'     => 'datetime',
			'renderer' => 'cronlog/adminhtml_widget_datetime',
			'index'    => 'executed_at',
			'sortable' => true,
		));

		$this->addColumn('finished_at', array(
			'header'   => $this->__('Finished At'),
			'width'    => '185px',
			'align'    => 'center',
			'type'     => 'datetime',
			'renderer' => 'cronlog/adminhtml_widget_datetime',
			'index'    => 'finished_at',
			'sortable' => true,
		));

		$this->addColumn('action', array(
			'header'  =>  $this->helper('adminhtml')->__('Action'),
			'width'   => '50px',
			'align'   => 'center',
			'type'    => 'action',
			'getter'  => 'getScheduleId',
			'actions' => array(
				array(
					'caption' => $this->__('Show'),
					'url'     => array('base' => '*/*/show'),
					'field'   => 'id'
				)
			),
			'sortable'  => false,
			'filter'    => false,
			'is_system' => true
		));

		return parent::_prepareColumns();
	}

	public function getRowUrl($row) {
		return $this->getUrl('*/*/show', array('id' => $row->getScheduleId()));
	}
}