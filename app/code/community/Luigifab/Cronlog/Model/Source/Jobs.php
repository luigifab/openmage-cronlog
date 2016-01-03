<?php
/**
 * Created D/10/02/2013
 * Updated S/07/02/2015
 * Version 16
 *
 * Copyright 2012-2016 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Cronlog_Model_Source_Jobs extends Varien_Data_Collection {

	public function getCollection($type = 'all') {

		// getName() = le nom du tag xml
		// => /config/crontab/jobs/cronlog_send_report
		// <crontab>
		//  <jobs>
		//   <cronlog_send_report>                         <= $config
		//    <run>
		//     <model>cronlog/observer::sendMail</model>
		//    <schedule>
		//     <disabled>1</disabled>
		$nodes = Mage::getConfig()->getXpath('/config/crontab/jobs/*');

		foreach ($nodes as $config) {

			$jobcode = $config->getName();
			$configurable = Mage::getConfig()->getNode('default/crontab/jobs/'.$jobcode);

			$expr  = (isset($config->schedule->config_path)) ? $config->schedule->config_path : null;
			$expr  = (isset($config->schedule->cron_expr))   ? $config->schedule->cron_expr   : $expr;
			$expr  = (isset($configurable->schedule->config_path)) ? $configurable->schedule->config_path : $expr;
			$expr  = (isset($configurable->schedule->cron_expr))   ? $configurable->schedule->cron_expr   : $expr;
			$expr  = (strlen(trim($expr)) > 0) ? trim($expr) : null;

			$model = (isset($config->run->model)) ? $config->run->model : null;
			$model = (isset($configurable->run->model)) ? $configurable->run->model : $model;

			$moduleName = Mage::getConfig()->getModelClassName($model);
			$moduleName = substr($moduleName, 0, strpos($moduleName, '_', strpos($moduleName, '_') + 1));
			$moduleName = str_replace('_', '/', $moduleName);

			// tâche désactivée si :
			// - la balise disabled
			// - ou configuration disabled
			// - ou pas de programmation (= ni balise config_path/cron_expr, ni configuration config_path/cron_expr)
			$isDisabled = (isset($config->schedule->disabled) || isset($configurable->schedule->disabled) || is_null($expr)) ?
				'disabled' : 'enabled';

			// tâche en lecture seule si :
			// - la balise disabled
			// - ou pas de balise de programmation (= pas de balise config_path/cron_expr)
			// - ou pas de programmation (= ni balise config_path/cron_expr, ni configuration config_path/cron_expr)
			$isReadOnly = (isset($config->schedule->disabled) ||
			               (!isset($config->schedule->config_path) && !isset($config->schedule->cron_expr)) ||
			               is_null($expr));

			$item = new Varien_Object();
			$item->setModule($moduleName);
			$item->setJobCode($jobcode);
			$item->setCronExpr($expr);
			$item->setModel($model);
			$item->setStatus($isDisabled);
			$item->setIsReadOnly($isReadOnly);

			if ((($type === 'ro') && $isReadOnly) || (($type === 'rw') && !$isReadOnly) || ($type === 'all'))
				$this->addItem($item);
		}

		usort($this->_items, array($this, 'sort'));
		return $this;
	}

	private function sort($a, $b) {
		return strcmp($a->getJobCode(), $b->getJobCode());
	}

	public function toOptionArray() {
		return array(
			array('label' => Mage::helper('cronlog')->__('Recent jobs'), 'value' => $this->getRecentOptionArray()),
			array('label' => Mage::helper('cronlog')->__('All jobs'), 'value' => $this->getCollection()->_toOptionArray('job_code', 'job_code')));
	}

	private function getRecentOptionArray() {

		$jobs = Mage::getResourceModel('cron/schedule_collection');
		$jobs->setOrder('executed_at', 'desc');
		$jobs->setPageSize(500);

		$data = array();
		$date = Mage::getSingleton('core/locale');

		foreach ($jobs as $job) {

			if (!isset($data[$job->getJobCode()]))
				$data[$job->getJobCode()] = array(
					'value' => $job->getJobCode(),
					'label' => $date->date($job->getScheduledAt(), Zend_Date::ISO_8601, null, true).' / '.$job->getJobCode()
				);

			if (count($data) > 9)
				break;
		}

		return $data;
	}
}