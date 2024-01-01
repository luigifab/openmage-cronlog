<?php
/**
 * Created L/25/05/2020
 * Updated J/28/12/2023
 *
 * Copyright 2012-2024 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

chdir(dirname($argv[0], 7)); // root
error_reporting(E_ALL);
ini_set('display_errors', (PHP_VERSION_ID < 80100) ? '1' : 1);

if (PHP_SAPI != 'cli')
	exit(-1);
if (is_file('maintenance.flag') || is_file('upgrade.flag'))
	exit(0);
if (is_file('app/bootstrap.php'))
	require_once('app/bootstrap.php');

$id  = empty($argv[1]) ? false : (int) $argv[1];
$dev = !empty($argv[2]);

if (!empty($id)) {

	require_once('app/Mage.php');
	Mage::app('admin')->setUseSessionInUrl(false);
	Mage::app()->addEventArea('crontab');
	Mage::setIsDeveloperMode($dev);

	$cron = Mage::getModel('cron/schedule')->load($id);
	if (!empty($cron->getId()) && ($cron->getData('status') == 'pending')) {

		try {
			// copy of Mage_Cron_Model_Observer::_processJob($cron, $jobConfig, $isAlways = false) without always
			$jobConfig = Mage::getConfig()->getNode('crontab/jobs')->{$cron->getData('job_code')};
			if (!empty($jobConfig->run->model)) {

				if (preg_match(Mage_Cron_Model_Observer::REGEX_RUN_MODEL, (string) $jobConfig->run->model, $run) !== 1)
					Mage::throwException(Mage::helper('cron')->__('Invalid model/method definition, expecting "model/class::method".'));

				$model = Mage::getModel($run[1]);
				if (!$model || !method_exists($model, $run[2]))
					Mage::throwException(Mage::helper('cron')->__('Invalid callback: %s::%s does not exist', $run[1], $run[2]));

				$callback  = [$model, $run[2]];
				$arguments = [$cron];
			}

			if (empty($callback))
				Mage::throwException(Mage::helper('cron')->__('No callbacks found'));

			$cron->setExecutedAt(date('Y-m-d H:i:s'))->save();
			call_user_func_array($callback, $arguments);
			$cron->setData('finished_at', date('Y-m-d H:i:s'));
			$cron->setData('status', ($cron->getIsError() === true) ? 'error' : 'success'); // PR 3310
		}
		catch (Throwable $t) {
			$cron->setData('status', 'error');
			$cron->setData('messages', get_class($t).': '.$t->getMessage()."\n".
				$t->getTraceAsString()."\n".'  thrown in '.$t->getFile().' on line '.$t->getLine());
		}

		$cron->save();
		exit(0);
	}
}

exit(-1);