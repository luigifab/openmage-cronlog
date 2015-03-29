<?php
/**
 * Created W/29/02/2012
 * Updated L/23/03/2015
 * Version 9
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

class Luigifab_Cronlog_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getVersion() {
		return (string) Mage::getConfig()->getModuleConfig('Luigifab_Cronlog')->version;
	}

	public function _($data, $a = null, $b = null) {
		return (strpos($txt = $this->__(' '.$data, $a, $b), ' ') === 0) ? $this->__($data, $a, $b) : $txt;
	}


	public function getDateToUtc($date) {

		$dt = new DateTime($date, new DateTimeZone(Mage::app()->getStore()->getConfig('general/locale/timezone')));
		$dt->setTimezone(new DateTimeZone('UTC'));

		return $dt->format('Y-m-d H:i:s');
	}

	public function getDateToLocal($date) {

		$dt = new DateTime($date, new DateTimeZone('UTC'));
		$dt->setTimezone(new DateTimeZone(Mage::app()->getStore()->getConfig('general/locale/timezone')));

		return $dt->format('Y-m-d H:i:s');
	}

	public function checkAndCorrectRunningStatus() {

		$transaction = Mage::getSingleton('core/resource')->getConnection('core_write');

		try {
			$transaction->beginTransaction();

			$jobs = Mage::getResourceModel('cron/schedule_collection');
			$jobs->addFieldToFilter('status', array('eq' => 'pending'));
			$jobs->addFieldToFilter('executed_at', array('notnull' => true));
			$jobs->addFieldToFilter('executed_at', array('neq' => '0000-00-00 00:00:00'));
			$jobs->addFieldToFilter('executed_at', array('neq' => ''));
			$jobs->addFieldToFilter('finished_at', array(array('null' => true), array('eq' => '0000-00-00 00:00:00'), array('eq' => '')));

			$ids = array();

			foreach ($jobs as $job) {
				$job->setStatus('running')->save();
				array_push($ids, $job->getId());
			}

			$transaction->commit();

			if (!empty($ids))
				Mage::getSingleton('adminhtml/session')
					->addNotice($this->__('Status changed for %d job(s) [%s] from "pending" to "running".', count($jobs), implode(' ', $ids)));
		}
		catch (Exception $e) {
			$transaction->rollback();
		}
	}
}