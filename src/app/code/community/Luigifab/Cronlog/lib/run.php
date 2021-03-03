<?php
/**
 * Created L/25/05/2020
 * Updated M/09/02/2021
 *
 * Copyright 2012-2021 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/openmage/cronlog
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

if (PHP_SAPI != 'cli')
	exit(-1);

chdir(dirname($argv[0], 7)); // de lib à root
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (is_file('maintenance.flag') || is_file('upgrade.flag'))
	exit(0);
if (is_file('app/bootstrap.php'))
	require_once('app/bootstrap.php');

$id  = empty($argv[1]) ? false : (int) $argv[1];
$dev = empty($argv[2]) ? false : true;

if (!empty($id)) {

	require_once('app/Mage.php');

	Mage::app('admin')->setUseSessionInUrl(false);
	Mage::app()->addEventArea('crontab');
	Mage::setIsDeveloperMode($dev);

	$job = Mage::getModel('cron/schedule')->load($id);
	if (!empty($job->getId()) && ($job->getData('status') == 'pending')) {

		try {
			// copie de Mage_Cron_Model_Observer::_processJob($job, $jobConfig, $isAlways = false) sauf les always
			$jobConfig = Mage::getConfig()->getNode('crontab/jobs')->{$job->getData('job_code')};

			if ($jobConfig->run->model) {
				if (!preg_match(Mage_Cron_Model_Observer::REGEX_RUN_MODEL, (string) $jobConfig->run->model, $run))
					Mage::throwException(Mage::helper('cron')->__('Invalid model/method definition, expecting "model/class::method".'));
				if (!($model = Mage::getModel($run[1])) || !method_exists($model, $run[2]))
					Mage::throwException(Mage::helper('cron')->__('Invalid callback: %s::%s does not exist', $run[1], $run[2]));
				$callback  = [$model, $run[2]];
				$arguments = [$job];
			}
			if (empty($callback)) {
				Mage::throwException(Mage::helper('cron')->__('No callbacks found'));
			}

			$job->setExecutedAt(date('Y-m-d H:i:s'))->save();
			call_user_func_array($callback, $arguments);
			$job->setData('finished_at', date('Y-m-d H:i:s'));
			$job->setData('status', 'success');
		}
		catch (Throwable $e) {
			$job->setData('status', Mage_Cron_Model_Schedule::STATUS_ERROR);
			$job->setData('messages', $e->__toString());
		}

		$job->save();
		exit(0);
	}
}

exit(-1);