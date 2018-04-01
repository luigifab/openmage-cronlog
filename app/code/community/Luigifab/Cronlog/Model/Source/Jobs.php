<?php
/**
 * Created D/10/02/2013
 * Updated S/03/03/2018
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
		$config = Mage::getModel('core/config')->loadBase()->loadModules()->loadDb();
		$nodes  = $config->getXpath('/config/crontab/jobs/*');

		foreach ($nodes as $config) {

			$jobcode = $config->getName();
			$configurable = Mage::getConfig()->getNode('default/crontab/jobs/'.$jobcode);

			$expr = (!empty($config->schedule->config_path)) ? Mage::getStoreConfig((string) $config->schedule->config_path) : null;
			$expr = (!empty($config->schedule->cron_expr))   ? $config->schedule->cron_expr : $expr;
			$expr = (!empty($configurable->schedule->config_path)) ? Mage::getStoreConfig((string) $configurable->schedule->config_path) : $expr;
			$expr = (!empty($configurable->schedule->cron_expr))   ? $configurable->schedule->cron_expr : $expr;
			$expr = (!empty(trim($expr))) ? trim($expr) : null;

			$model = (!empty($config->run->model)) ? $config->run->model : null;
			$model = (!empty($configurable->run->model)) ? $configurable->run->model : $model;

			$moduleName = Mage::getConfig()->getModelClassName($model);
			$moduleName = substr($moduleName, 0, strpos($moduleName, '_', strpos($moduleName, '_') + 1));
			$moduleName = str_replace('_', '/', $moduleName);

			// tâche désactivée si :
			// - la balise disabled
			// - ou configuration disabled
			// - ou pas de programmation (= ni balise config_path/cron_expr, ni configuration config_path/cron_expr)
			$isDisabled = (!empty($config->schedule->disabled) || !empty($configurable->schedule->disabled) || empty($expr)) ?
				'disabled' : 'enabled';

			// tâche en lecture seule si :
			// - la balise disabled
			// - ou pas de balise de programmation (= pas de balise config_path/cron_expr)
			// - ou pas de programmation (= ni balise config_path/cron_expr, ni configuration config_path/cron_expr)
			$isReadOnly = (!empty($config->schedule->disabled) ||
			               (empty($config->schedule->config_path) && empty($config->schedule->cron_expr)) ||
			               empty($expr));

			$item = new Varien_Object();
			$item->setData('module', $moduleName);
			$item->setData('job_code', $jobcode);
			$item->setData('cron_expr', $expr);
			$item->setData('model', $model);
			$item->setData('status', $isDisabled);
			$item->setData('is_read_only', $isReadOnly);

			if ((($type == 'ro') && $isReadOnly) || (($type == 'rw') && !$isReadOnly) || ($type == 'all'))
				$this->addItem($item);
		}

		usort($this->_items, array($this, 'sort'));
		return $this;
	}

	private function sort($a, $b) {
		return strcmp($a->getData('job_code'), $b->getData('job_code'));
	}

	public function toOptionArray() {
		return array(
			array('label' => Mage::helper('cronlog')->__('Recent jobs'), 'value' => $this->getRecentOptionArray()),
			array('label' => Mage::helper('cronlog')->__('All jobs'), 'value' => $this->getCollection()->_toOptionArray('job_code', 'job_code'))
		);
	}

	private function getRecentOptionArray() {

		$data = array();
		$date = Mage::getSingleton('core/locale'); //date($date, $format, $locale null, $useTimezone null)
		$jobs = Mage::getResourceModel('cron/schedule_collection')->setOrder('executed_at', 'desc')->setPageSize(500);

		foreach ($jobs as $job) {

			if (!empty($data[$job->getData('job_code')]))
				continue;
			if (count($data) > 10)
				break;

			$data[$job->getData('job_code')] = array(
				'value' => $job->getData('job_code'),
				'label' => $date->date($job->getData('scheduled_at'), Zend_Date::ISO_8601, null, true).' / '.$job->getData('job_code')
			);
		}

		return $data;
	}
}