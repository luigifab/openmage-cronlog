<?php
/**
 * Created S/31/05/2014
 * Updated M/24/09/2019
 *
 * Copyright 2012-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/magento/cronlog
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

class Luigifab_Cronlog_Model_Rewrite_Observer extends Mage_Cron_Model_Observer {

	protected function _generateJobs($jobs, $exists) {

		$items = [];

		foreach ($jobs as $code => $config) {
			if (empty($config->schedule->disabled) && empty(Mage::getStoreConfig('crontab/jobs/'.$code.'/schedule/disabled')))
				$items[$code] = $config;
		}

		return parent::_generateJobs($items, $exists);
	}

	public function cleanup() {

		$val = (int) Mage::getStoreConfig('cronlog/general/lifetime');
		if ($val < 7200) // 5 jours = 7200 minutes
			return parent::cleanup();

		// 24 heures = 86400 secondes
		if (Mage::app()->loadCache(self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT) > (time() - 86400))
			return $this;

		$jobs = Mage::getResourceModel('cron/schedule_collection');
		$jobs->addFieldToFilter('status', ['eq' => 'success']);
		$jobs->addFieldToFilter('scheduled_at', ['lt' => new Zend_Db_Expr('DATE_SUB(UTC_TIMESTAMP(), INTERVAL '.$val.' MINUTE)')]);

		foreach ($jobs as $job)
			$job->delete();

		$jobs = Mage::getResourceModel('cron/schedule_collection');
		$jobs->addFieldToFilter('status', ['neq' => 'success']);
		$jobs->addFieldToFilter('scheduled_at', ['lt' => new Zend_Db_Expr('DATE_SUB(UTC_TIMESTAMP(), INTERVAL '.(3 * $val).' MINUTE)')]);

		foreach ($jobs as $job)
			$job->delete();

		Mage::app()->saveCache(time(), self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT, ['crontab'], null);
		return $this;
	}
}