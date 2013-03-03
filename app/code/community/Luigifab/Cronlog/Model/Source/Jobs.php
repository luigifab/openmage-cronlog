<?php
/**
 * Created D/10/02/2013
 * Updated S/02/03/2013
 * Version 3
 *
 * Copyright 2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Cronlog_Model_Source_Jobs {

	public function toOptionArray() {

		return array(
			array(
				'label' => Mage::helper('cronlog')->__('Recent jobs'),
				'value' => $this->getRecentJobs()
			),
			array(
				'label' => Mage::helper('cronlog')->__('All jobs'),
				'value' => $this->getAllJobs()
			)
		);
	}

	private function getRecentJobs() {

		$jobs = Mage::getResourceModel('cron/schedule_collection');
		$jobs->setOrder('executed_at', 'desc');
		$jobs->setPageSize(200);

		$data = array();
		$date = Mage::getSingleton('core/locale');

		foreach ($jobs as $job) {

			if (!isset($data[$job->getJobCode()])) {
				$data[$job->getJobCode()] = array(
					'value' => $job->getJobCode(),
					'label' => $date->date($job->getScheduledAt(), Zend_Date::ISO_8601, null, true).' : '.$job->getJobCode()
				);
			}

			if (count($data) >= 10)
				break;
		}

		return $data;
	}

	private function getAllJobs() {

		$jobs = (array) Mage::getConfig()->getNode('crontab/jobs');
		$data = array();

		foreach ($jobs as $job => $config)
			$data[$job] = array('value' => $job, 'label' => $job);

		ksort($data);
		return $data;
	}
}