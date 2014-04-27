<?php
/**
 * Created J/17/05/2012
 * Updated D/27/04/2014
 * Version 14
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

class Luigifab_Cronlog_Model_Observer {

	// #### Envoi du rapport par email ###################################### i18n ## public ### //
	// = révision : 23
	// » Génère le rapport pour la veille en fonction de la configuration (quotidien/hebdomadaire/mensuel)
	// » Envoi le rapport via un email transactionnel
	public function sendMail() {

		Mage::getSingleton('core/translate')->setLocale(Mage::getStoreConfig('general/locale/code'))->init('adminhtml', true);

		$frequency = Mage::getStoreConfig('cronlog/email/frequency');
		$date = Mage::getSingleton('core/locale'); // date($date, $format, $locale = null, $useTimezone = null)

		$stats = $errors = array();

		// chargement des tâches cron de la période
		if ($frequency === Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY) {
			$range = $this->getDateRange('1m');
			$frequency = Mage::helper('cronlog')->__('monthly');
		}
		else if ($frequency === Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY) {
			$range = $this->getDateRange('7d');
			$frequency = Mage::helper('cronlog')->__('weekly');
		}
		else {
			$range = $this->getDateRange('24h');
			$frequency = Mage::helper('cronlog')->__('daily');
		}

		$collection = Mage::getResourceModel('cron/schedule_collection');
		$collection->addFieldToFilter('created_at', $range);
		$collection->getSelect()->order('schedule_id', 'DESC');

		//echo '<p>',$collection->getSelect(),'</p>';
		//echo '<p>',count($collection),'</p>';

		foreach ($collection as $job) {

			if (!isset($stats[$job->getStatus()]))
				$stats[$job->getStatus()] = array();

			$stats[$job->getStatus()][] = $job->getId();

			if (in_array($job->getStatus(), array('error', 'missed'))) {

				$link   = str_replace('//admin', '/admin', '<a href="'.Mage::helper('adminhtml')->getUrl('adminhtml/cronlog_history/view', array('id' => $job->getId())).'" style="color:red;">'.Mage::helper('cronlog')->__('Job').' '.$job->getId().'</a>');
				$hour   = Mage::helper('cronlog')->__('Scheduled At: %s', $date->date($job->getScheduledAt(), Zend_Date::ISO_8601));
				$status = Mage::helper('cronlog')->__('Status: %s (%s)', Mage::helper('cronlog')->__(ucfirst($job->getStatus())), $job->getStatus());
				$error  = '<pre style="margin:0.5em; font-size:0.9em; color:gray; white-space:pre-wrap;">'.$job->getMessages().'</pre>';

				$errors[] = '('.(count($errors) + 1).') '.$link.' / '.$hour.' / '.$status.' '.$error;
			}
		}

		// envoie de l'email
		// sendTransactional($templateId, $sender, $recipient, $name, $vars = array(), $storeId = null)
		$emailsAddresses = explode(' ', trim(Mage::getStoreConfig('cronlog/email/recipient_email')));
		$backend = Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit', array('section' => 'cronlog'));

		$variables = array(
			'frequency'     => $frequency,
			'date_period_from' => $date->date($range['from'], Zend_Date::ISO_8601)->toString(Zend_Date::DATETIME_FULL),
			'date_period_to'   => $date->date($range['to'], Zend_Date::ISO_8601)->toString(Zend_Date::DATETIME_FULL),
			'total_cron'    => count($collection),
			'total_pending' => (isset($stats['pending'])) ? count($stats['pending']) : 0,
			'total_running' => (isset($stats['running'])) ? count($stats['running']) : 0,
			'total_success' => (isset($stats['success'])) ? count($stats['success']) : 0,
			'total_missed'  => (isset($stats['missed'])) ? count($stats['missed']) : 0,
			'total_error'   => (isset($stats['error'])) ? count($stats['error']) : 0,
			'errors_list'   => (count($errors) > 0) ? '<li style="margin:0.5em 0;">'.implode('</li><li style="margin:0.5em 0;">', $errors).'</li>' : '',
			'config_url'    => str_replace('//admin', '/admin', $backend)
		);

		foreach ($emailsAddresses as $emailAddress) {

			$email = Mage::getModel('core/email_template');
			$email->sendTransactional(
				Mage::getStoreConfig('cronlog/email/template'),
				Mage::getStoreConfig('cronlog/email/sender_email_identity'),
				trim($emailAddress),
				null,
				$variables
			);

			if (!$email->getSentSuccess())
				throw new Exception(Mage::helper('cronlog')->__('Can not send email report to %s.', $emailAddress));

			//exit($email->getProcessedTemplate($variables));
		}
	}


	// #### Programmation de la tâche cron ########################################## public ### //
	// = révision : 11
	// » Quotidien : tous les jours à 01h00 (quotidien/daily)
	// » Hebdomadaire : tous les lundi à 01h00 (hebdomadaire/weekly)
	// » Mensuel : chaque premier jour du mois à 01h00 (mensuel/monthly)
	public function updateConfig() {

		try {
			$config = Mage::getModel('core/config_data');
			$config->load('crontab/jobs/cronlog_send_report/schedule/cron_expr', 'path');

			if (Mage::getStoreConfig('cronlog/email/enabled') === '1') {

				$frequency = Mage::getStoreConfig('cronlog/email/frequency');

				// minute hour day-of-month month-of-year day-of-week (Dimanche = 0, Lundi = 1...)
				// 00	 01   1			*			 *		   => monthly
				// 00	 01   *			*			 0|1		   => weekly
				// 00	 01   *			*			 *		   => daily
				if ($frequency === Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY)
					$config->setValue('00 01 1 * *');
				else if ($frequency === Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY)
					$config->setValue('00 01 * * '.Mage::getStoreConfig('general/locale/firstday'));
				else
					$config->setValue('00 01 * * *');

				$config->setPath('crontab/jobs/cronlog_send_report/schedule/cron_expr');
				$config->save();

				// envoi d'un email de test
				$this->sendMail();
			}
			else {
				$config->delete();
			}
		}
		catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}


	// #### Génération des périodes ################################################ private ### //
	// = révision : 12
	// » Renvoie une période : hier, la semaine dernière ou le mois dernier
	// » Doit être appelé pour le calcul des dates de la veille uniquement
	private function getDateRange($range) {

		$dateStart = Mage::app()->getLocale()->date();
		$dateStart->setTimezone(Mage::getStoreConfig('general/locale/timezone'));
		$dateStart->setHour(0);
		$dateStart->setMinute(0);
		$dateStart->setSecond(0);

		$dateEnd = Mage::app()->getLocale()->date();
		$dateEnd->setTimezone(Mage::getStoreConfig('general/locale/timezone'));
		$dateEnd->setHour(23);
		$dateEnd->setMinute(59);
		$dateEnd->setSecond(59);

		if ($range === '24h') {
			$dateStart->subDay(1);
			$dateEnd->subDay(1);
		}
		else if ($range === '7d') {
			$dateStart->subDay(7);
			$dateEnd->subDay(1);
		}
		else if ($range === '1m') {
			$dateStart->subDay(1)->setDay(1);
			$dateEnd->subDay(1);
		}

		return array(
			'datetime' => true,
			'from' => Mage::helper('cronlog')->getDateToUtc($dateStart->toString(Zend_Date::RFC_3339)),
			'to' => Mage::helper('cronlog')->getDateToUtc($dateEnd->toString(Zend_Date::RFC_3339))
		);
	}
}