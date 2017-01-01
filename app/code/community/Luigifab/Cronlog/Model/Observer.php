<?php
/**
 * Created J/17/05/2012
 * Updated M/08/11/2016
 *
 * Copyright 2012-2017 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Cronlog_Model_Observer extends Luigifab_Cronlog_Helper_Data {

	// EVENT admin_system_config_changed_section_cronlog
	public function updateConfig() {

		try {
			$config = Mage::getModel('core/config_data');
			$config->load('crontab/jobs/cronlog_send_report/schedule/cron_expr', 'path');

			if (Mage::getStoreConfigFlag('cronlog/email/enabled')) {

				// quotidien, tous les jours à 1h00 (quotidien/daily)
				// hebdomadaire, tous les lundi à 1h00 (hebdomadaire/weekly)
				// mensuel, chaque premier jour du mois à 1h00 (mensuel/monthly)
				$frequency = Mage::getStoreConfig('cronlog/email/frequency');

				// minute hour day-of-month month-of-year day-of-week (Dimanche = 0, Lundi = 1...)
				// 0	     1    1            *             *           => monthly
				// 0	     1    *            *             0|1         => weekly
				// 0	     1    *            *             *           => daily
				if ($frequency === Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY)
					$config->setValue('0 1 1 * *');
				else if ($frequency === Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY)
					$config->setValue('0 1 * * '.Mage::getStoreConfig('general/locale/firstday'));
				else
					$config->setValue('0 1 * * *');

				$config->setPath('crontab/jobs/cronlog_send_report/schedule/cron_expr');
				$config->save();

				// email de test
				// s'il n'a pas déjà été envoyé dans la dernière heure (3600 secondes)
				// ou si le cookie maillog_print_email est présent, et ce, quoi qu'il arrive
				$cookie = (Mage::getSingleton('core/cookie')->get('maillog_print_email') === 'yes') ? true : false;
				$session = Mage::getSingleton('admin/session')->getLastCronlogReport();
				$timestamp = Mage::getSingleton('core/date')->timestamp();

				if (is_null($session) || ($timestamp > ($session + 3600)) || $cookie) {
					$this->sendEmailReport();
					Mage::getSingleton('admin/session')->setLastCronlogReport($timestamp);
				}
			}
			else {
				$config->delete();
			}
		}
		catch (Exception $e) {
			Mage::throwException($e->getMessage());
		}
	}


	// CRON cronlog_send_report
	public function sendEmailReport() {

		Mage::getSingleton('core/translate')->setLocale(Mage::getStoreConfig('general/locale/code'))->init('adminhtml', true);
		$frequency = Mage::getStoreConfig('cronlog/email/frequency');
		$errors = array();

		// chargement des tâches cron de la période
		// le mois dernier (mensuel/monthly), les septs derniers jour (hebdomadaire/weekly), hier (quotidien/daily)
		$dateStart = Mage::getSingleton('core/locale')->date();
		$dateStart->setTimezone(Mage::getStoreConfig('general/locale/timezone'));
		$dateStart->setHour(0);
		$dateStart->setMinute(0);
		$dateStart->setSecond(0);

		$dateEnd = Mage::getSingleton('core/locale')->date();
		$dateEnd->setTimezone(Mage::getStoreConfig('general/locale/timezone'));
		$dateEnd->setHour(23);
		$dateEnd->setMinute(59);
		$dateEnd->setSecond(59);

		if ($frequency === Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY) {
			$frequency = $this->__('monthly');
			$dateStart->subMonth(1)->setDay(1);
			$dateEnd->subMonth(1)->setDay(1);
			$dateEnd->setDay($dateEnd->toString(Zend_Date::MONTH_DAYS));
			// Évite ce genre de chose... (date(n) = numéro du mois, date(t)/Zend_Date::MONTH_DAYS = nombre de jour du mois)
			// Période du dimanche 1 mars 2015 00:00:00 Europe/Paris au samedi 28 février 2015 23:59:59 Europe/Paris
			// Il est étrange que la variable dateEnd ne soit pas affectée
			if (date('n', $dateStart->getTimestamp()) === date('n', $dateEnd->getTimestamp()))
				$dateStart->subDay($dateStart->toString(Zend_Date::MONTH_DAYS));
		}
		else if ($frequency === Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY) {
			$frequency = $this->__('weekly');
			$dateStart->subDay(7);
			$dateEnd->subDay(1);
		}
		else {
			$frequency = $this->__('daily');
			$dateStart->subDay(1);
			$dateEnd->subDay(1);
		}

		// chargement des tâches cron
		$jobs = Mage::getResourceModel('cron/schedule_collection');
		$jobs->setOrder('schedule_id', 'desc');
		$jobs->addFieldToFilter('created_at', array(
			'datetime' => true,
			'from' => $dateStart->toString(Zend_Date::RFC_3339), 'to' => $dateEnd->toString(Zend_Date::RFC_3339)
		));

		foreach ($jobs as $job) {

			if (!in_array($job->getStatus(), array('error', 'missed')))
				continue;

			$link = '<a href="'.$this->getEmailUrl('adminhtml/cronlog_history/view', array('id' => $job->getId())).'" style="font-weight:bold; color:red; text-decoration:none;">'.$this->__('Job %d: %s', $job->getId(), $job->getJobCode()).'</a>';

			$hour  = $this->_('Scheduled At: %s', Mage::getSingleton('core/locale')->date($job->getScheduledAt(), Zend_Date::ISO_8601));
			$state = $this->__('Status: %s (%s)', $this->__(ucfirst($job->getStatus())), $job->getStatus());
			$error = '<pre style="margin:0.5em; font-size:0.9em; color:gray; white-space:pre-wrap;">'.$job->getMessages().'</pre>';

			array_push($errors, sprintf('(%d) %s / %s / %s %s', count($errors) + 1, $link, $hour, $state, $error));
		}

		// envoi des emails
		$this->sendReportToRecipients(array(
			'frequency'        => $frequency,
			'date_period_from' => $dateStart->toString(Zend_Date::DATETIME_FULL),
			'date_period_to'   => $dateEnd->toString(Zend_Date::DATETIME_FULL),
			'total_cron'       => count($jobs),
			'total_pending'    => count($jobs->getItemsByColumnValue('status', 'pending')),
			'total_running'    => count($jobs->getItemsByColumnValue('status', 'running')),
			'total_success'    => count($jobs->getItemsByColumnValue('status', 'success')),
			'total_missed'     => count($jobs->getItemsByColumnValue('status', 'missed')),
			'total_error'      => count($jobs->getItemsByColumnValue('status', 'error')),
			'list'             => (count($errors) > 0) ? implode('</li><li style="margin:0.8em 0 0.5em;">', $errors) : ''
		));
	}

	private function getEmailUrl($url, $params = array()) {

		if (Mage::getStoreConfigFlag('web/seo/use_rewrites'))
			return preg_replace('#/[^/]+\.php/#', '/', Mage::helper('adminhtml')->getUrl($url, $params));
		else
			return preg_replace('#/[^/]+\.php/#', '/index.php/', Mage::helper('adminhtml')->getUrl($url, $params));
	}

	private function sendReportToRecipients($vars) {

		$emails = explode(' ', trim(Mage::getStoreConfig('cronlog/email/recipient_email')));
		$vars['config'] = $this->getEmailUrl('adminhtml/system/config');
		$vars['config'] = substr($vars['config'], 0, strrpos($vars['config'], '/system/config'));

		foreach ($emails as $email) {

			if (in_array($email, array('hello@example.org', 'hello@example.com', '')))
				continue;

			// sendTransactional($templateId, $sender, $recipient, $name, $vars = array(), $storeId = null)
			$template = Mage::getModel('core/email_template');
			$template->sendTransactional(
				Mage::getStoreConfig('cronlog/email/template'),
				Mage::getStoreConfig('cronlog/email/sender_email_identity'),
				trim($email), null, $vars
			);

			if (!$template->getSentSuccess())
				Mage::throwException($this->__('Can not send the report by email to %s.', $email));

			//exit($template->getProcessedTemplate($vars));
		}
	}
}