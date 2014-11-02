<?php
/**
 * Created W/29/02/2012
 * Updated V/29/08/2014
 * Version 12
 *
 * Copyright 2012-2014 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

		$this->setId('cronlog_grid');
		$this->setDefaultSort('schedule_id');
		$this->setDefaultDir('DESC');

		$this->setUseAjax(true);
		$this->setSaveParametersInSession(true);
		$this->setPagerVisibility(true);
		$this->setFilterVisibility(true);
		$this->setDefaultLimit(max($this->_defaultLimit, intval(Mage::getStoreConfig('cronlog/general/number'))));
	}

	protected function _prepareCollection() {

		$this->setCollection(Mage::getResourceModel('cron/schedule_collection'));

		if ((strlen(Mage::getStoreConfig('cronlog/general/filter')) > 0) && (strlen($this->getParam($this->getVarNameFilter(), '')) < 10)) {
			$filter = $this->getParam($this->getVarNameFilter(), array('status' => Mage::getStoreConfig('cronlog/general/filter')));
			$this->_setFilterValues($filter);
		}

		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {

		$jobs = Mage::getResourceModel('cron/schedule_collection');

		// filtre jobcode en mode texte ou en mode liste
		// force le mode texte si la valeur actuelle du filtre n'est pas trouvée dans la liste des possibilités
		// quoi qu'il arrive, utilise le filtre en liste si filter=true et si textmode=0
		$codes = Mage::getModel('cronlog/source_jobs')->getCollection()->getColumnValues('job_code');
		$codes = array_combine($codes, $codes);

		$filter = $this->getParam($this->getVarNameFilter(), true);

		if (is_string($filter)) {

			$filter = $this->helper('adminhtml')->prepareFilterString($filter);

			if (isset($filter['job_code']) && (strlen($filter['job_code']) > 0)) {
				$jobs->addFieldToFilter('job_code', array('like' => '%'.$filter['job_code'].'%'));
				$filter = (in_array($filter['job_code'], $codes)) ? true : false;
			}
			else {
				$filter = true;
			}
		}

		// comptage des tâches
		// en fonction du filtrage par jobcode
		$pending = count($jobs->getItemsByColumnValue('status', 'pending'));
		$running = count($jobs->getItemsByColumnValue('status', 'running'));
		$missed  = count($jobs->getItemsByColumnValue('status', 'missed'));
		$error   = count($jobs->getItemsByColumnValue('status', 'error'));
		$success = count($jobs) - $pending - $running - $missed - $error;

		// définition des colonnes
		$this->addColumn('schedule_id', array(
			'header'    => $this->helper('adminhtml')->__('Id'),
			'index'     => 'schedule_id',
			'align'     => 'center',
			'width'     => '80px',
			'sortable'  => true
		));

		if ($filter && (Mage::getStoreConfig('cronlog/general/textmode') !== '1')) {
			$this->addColumn('job_code', array(
				'header'    => $this->__('Job'),
				'index'     => 'job_code',
				'type'      => 'options',
				'renderer'  => 'cronlog/adminhtml_widget_code',
				'options'   => $codes,
				'align'     => 'center',
				'sortable'  => true
			));
		}
		else {
			$this->addColumn('job_code', array(
				'header'    => $this->__('Job'),
				'index'     => 'job_code',
				'align'     => 'center',
				'sortable'  => true
			));
		}

		$this->addColumn('created_at', array(
			'header'    => $this->__('Created At'),
			'index'     => 'created_at',
			'type'      => 'datetime',
			'renderer'  => 'cronlog/adminhtml_widget_datetime',
			'align'     => 'center',
			'width'     => '180px',
			'sortable'  => true
		));

		$this->addColumn('scheduled_at', array(
			'header'    => $this->__('Scheduled At'),
			'index'     => 'scheduled_at',
			'type'      => 'datetime',
			'renderer'  => 'cronlog/adminhtml_widget_datetime',
			'align'     => 'center',
			'width'     => '180px',
			'sortable'  => true
		));

		$this->addColumn('executed_at', array(
			'header'    => $this->__('Executed At'),
			'index'     => 'executed_at',
			'type'      => 'datetime',
			'renderer'  => 'cronlog/adminhtml_widget_datetime',
			'align'     => 'center',
			'width'     => '180px',
			'sortable'  => true
		));

		$this->addColumn('finished_at', array(
			'header'    => $this->__('Finished At'),
			'index'     => 'finished_at',
			'type'      => 'datetime',
			'renderer'  => 'cronlog/adminhtml_widget_datetime',
			'align'     => 'center',
			'width'     => '180px',
			'sortable'  => true
		));

		$this->addColumn('duration', array(
			'header'    => $this->__('Duration'),
			'index'     => 'duration',
			'renderer'  => 'cronlog/adminhtml_widget_duration',
			'align'     => 'center',
			'width'     => '60px',
			'filter'    => false,
			'sortable'  => false
		));

		$this->addColumn('status', array(
			'header'    => $this->helper('adminhtml')->__('Status'),
			'index'     => 'status',
			'type'      => 'options',
			'renderer'  => 'cronlog/adminhtml_widget_status',
			'options'   => array(
				'pending' => $this->__('Pending (%d)', $pending),
				'running' => $this->__('Running (%d)', $running),
				'success' => $this->__('Success (%d)', $success),
				'missed'  => $this->__('Missed (%d)', $missed),
				'error'   => $this->__('Error (%d)', $error)
			),
			'align'     => 'status',
			'width'     => '125px',
			'sortable'  => true
		));

		$this->addColumn('action', array(
			'type'      => 'action',
			'getter'    => 'getId',
			'actions'   => array(
				array(
					'caption' => $this->helper('adminhtml')->__('View'),
					'url'     => array('base' => '*/*/view'),
					'field'   => 'id'
				)
			),
			'align'     => 'center',
			'width'     => '55px',
			'filter'    => false,
			'sortable'  => false,
			'is_system' => true
		));

		return parent::_prepareColumns();
	}

	public function getRowUrl($row) {
		return $this->getUrl('*/*/view', array('id' => $row->getId()));
	}
}