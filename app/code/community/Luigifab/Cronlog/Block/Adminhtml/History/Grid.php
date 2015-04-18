<?php
/**
 * Created W/29/02/2012
 * Updated S/11/04/2015
 * Version 20
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
				$filter = (in_array($filter['job_code'], $codes));
			}
			else {
				$filter = true;
			}
		}

		// comptage des tâches
		// en fonction de l'éventuel filtrage par jobcode
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
			'width'     => '80px'
		));

		if ($filter && (Mage::getStoreConfig('cronlog/general/textmode') !== '1')) {
			$this->addColumn('job_code', array(
				'header'    => $this->__('Job'),
				'index'     => 'job_code',
				'type'      => 'options',
				'options'   => $codes,
				'align'     => 'center',
				'frame_callback' => array($this, 'decorateCode')
			));
		}
		else {
			$this->addColumn('job_code', array(
				'header'    => $this->__('Job'),
				'index'     => 'job_code',
				'align'     => 'center'
			));
		}

		$this->addColumn('created_at', array(
			'header'    => $this->helper('cronlog')->_('Created At'),
			'index'     => 'created_at',
			'type'      => 'datetime',
			'align'     => 'center',
			'width'     => '180px',
			'frame_callback' => array($this, 'decorateDate')
		));

		$this->addColumn('scheduled_at', array(
			'header'    => $this->helper('cronlog')->_('Scheduled At'),
			'index'     => 'scheduled_at',
			'type'      => 'datetime',
			'align'     => 'center',
			'width'     => '180px',
			'frame_callback' => array($this, 'decorateDate')
		));

		$this->addColumn('executed_at', array(
			'header'    => $this->helper('cronlog')->_('Executed At'),
			'index'     => 'executed_at',
			'type'      => 'datetime',
			'align'     => 'center',
			'width'     => '180px',
			'frame_callback' => array($this, 'decorateDate')
		));

		$this->addColumn('finished_at', array(
			'header'    => $this->helper('cronlog')->_('Finished At'),
			'index'     => 'finished_at',
			'type'      => 'datetime',
			'align'     => 'center',
			'width'     => '180px',
			'frame_callback' => array($this, 'decorateDate')
		));

		$this->addColumn('duration', array(
			'header'    => $this->__('Duration'),
			'index'     => 'duration',
			'align'     => 'center',
			'width'     => '60px',
			'filter'    => false,
			'sortable'  => false,
			'frame_callback' => array($this, 'decorateDuration')
		));

		$this->addColumn('status', array(
			'header'    => $this->helper('adminhtml')->__('Status'),
			'index'     => 'status',
			'type'      => 'options',
			'options'   => array(
				'pending' => $this->__('Pending (%d)', $pending),
				'running' => $this->__('Running (%d)', $running),
				'success' => $this->__('Success (%d)', $success),
				'missed'  => $this->__('Missed (%d)', $missed),
				'error'   => $this->__('Error (%d)', $error)
			),
			'align'     => 'status',
			'width'     => '125px',
			'frame_callback' => array($this, 'decorateStatus')
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


	public function getRowClass($row) {
		return '';
	}

	public function getRowUrl($row) {
		return $this->getUrl('*/*/view', array('id' => $row->getId()));
	}

	public function decorateStatus($value, $row, $column, $isExport) {

		$status = (strpos($value, ' (') !== false) ? substr($value, 0, strpos($value, ' (')) : $value;
		return '<span class="grid-'.$row->getData('status').'">'.trim($status).'</span>';
	}

	public function decorateDuration($value, $row, $column, $isExport) {

		if (!in_array($row->getData('executed_at'), array('', '0000-00-00 00:00:00', null)) &&
		    !in_array($row->getData('finished_at'), array('', '0000-00-00 00:00:00', null))) {

			$data = strtotime($row->getData('finished_at')) - strtotime($row->getData('executed_at'));
			$minutes = intval($data / 60);
			$seconds = intval($data % 60);

			if ($data > 599)
				$data = '<strong>'.(($seconds > 9) ? $minutes.':'.$seconds : $minutes.':0'.$seconds).'</strong>';
			else if ($data > 59)
				$data = '<strong>'.(($seconds > 9) ? '0'.$minutes.':'.$seconds : '0'.$minutes.':0'.$seconds).'</strong>';
			else if ($data > 0)
				$data = ($seconds > 9) ? '00:'.$data : '00:0'.$data;
			else
				$data = '&lt; 1';

			return $data;
		}
	}

	public function decorateDate($value, $row, $column, $isExport) {
		return (!in_array($row->getData($column->getIndex()), array('', '0000-00-00 00:00:00', null))) ? $value : '';
	}

	public function decorateCode($value, $row, $column, $isExport) {
		return $row->getData('job_code');
	}
}