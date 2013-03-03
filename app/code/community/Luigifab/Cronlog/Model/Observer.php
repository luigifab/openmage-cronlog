<?php
/**
 * Created J/17/05/2012
 * Updated L/04/03/2013
 * Version 12
 *
 * Copyright 2012-2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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
	// = révision : 13
	// » Génère le rapport pour la veille en fonction de la configuration (quotidien/hebdomadaire/mensuel)
	// » S'assure que la langue soit correctement définie
	// » Envoi le rapport via un email transactionnel
	public function sendMail() {

		$lang = Mage::getStoreConfig('general/locale/code');
		Mage::getSingleton('core/translate')->setLocale($lang)->init('adminhtml', true);

		$frequency = Mage::getStoreConfig('cronlog/email/frequency');
		$locale = Mage::getSingleton('core/locale');
		$helper = Mage::helper('cronlog');

		$daily = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY;
		$weekly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
		$monthly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;

		$texts = array($daily => $helper->__('daily'), $weekly => $helper->__('weekly'), $monthly => $helper->__('monthly'));
		$stats = array();

		// chargement des tâches cron de la période
		if ($frequency === $daily) {
			$range = $this->getDateRange('24h');
			$collection = Mage::getResourceModel('cron/schedule_collection');
			$collection->addFieldToFilter('created_at', $range);
		}
		else if ($frequency === $weekly) {
			$range = $this->getDateRange('7d');
			$collection = Mage::getResourceModel('cron/schedule_collection');
			$collection->addFieldToFilter('created_at', $range);
		}
		else if ($frequency === $monthly) {
			$range = $this->getDateRange('1m');
			$collection = Mage::getResourceModel('cron/schedule_collection');
			$collection->addFieldToFilter('created_at', $range);
		}

		//echo '<p>',$collection->getSelect(),'</p>';
		//echo '<p>',count($collection),'</p>';

		foreach ($collection as $job) {

			if (!isset($stats[$job->getStatus()]))
				$stats[$job->getStatus()] = array();

			$stats[$job->getStatus()][] = $job->getId();
		}

		// envoie de l'email
		// sendTransactional($templateId, $sender, $recipient, $name, $vars = array(), $storeId = null)
		$offset = Mage::getModel('core/date')->timestamp(time()) - time();

		$from = $locale->date(date('c', $range['fromTime'] + $offset), Zend_Date::ISO_8601, null, false);
		$to = $locale->date(date('c', $range['toTime'] + $offset), Zend_Date::ISO_8601, null, false);

		$from = substr($from, 0, strrpos($from, ' '));
		$to = substr($to, 0, strrpos($to, ' '));

		$emailsAddresses = explode(' ', trim(Mage::getStoreConfig('cronlog/email/recipient_email')));
		$backendUrl = Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit', array('section' => 'cronlog'));

		foreach ($emailsAddresses as $emailAddress) {

			$email = Mage::getModel('core/email_template');
			$email->sendTransactional(
				Mage::getStoreConfig('cronlog/email/template'),
				Mage::getStoreConfig('cronlog/email/sender_email_identity'),
				trim($emailAddress),
				null,
				array(
					'frequency'  => $texts[$frequency],
					'total_cron' => count($collection),
					'total_pending' => (isset($stats['pending'])) ? count($stats['pending']) : 0,
					'total_running' => (isset($stats['running'])) ? count($stats['running']) : 0,
					'total_success' => (isset($stats['success'])) ? count($stats['success']) : 0,
					'total_missed'  => (isset($stats['missed'])) ? count($stats['missed']) : 0,
					'total_error'   => (isset($stats['error'])) ? count($stats['error']) : 0,
					'config_url'  => str_replace('//admin', '/admin', $backendUrl),
					'date_period' => ($from !== $to) ? $helper->__('from <strong>%s</strong> to <strong>%s</strong> included', $from, $to) : $helper->__('from <strong>%s</strong>', $from)
				)
			);

			if (!$email->getSentSuccess())
				throw new Exception($helper->__('Can not send email report to %s.', $emailAddress));
		}
	}


	// #### Programmation de la tâche cron ########################################## public ### //
	// = révision : 8
	// » Quotidien : tous les jours à 01h15 (quotidien/daily)
	// » Hebdomadaire : tous les lundi à 01h15 (hebdomadaire/weekly)
	// » Mensuel : chaque premier jour du mois à 01h15 (mensuel/monthly)
	public function updateConfig() {

		try {
			$config = Mage::getModel('core/config_data');
			$config->load('crontab/jobs/cronlog_send_report/schedule/cron_expr', 'path');

			if (Mage::getStoreConfig('cronlog/email/enabled') === '1') {

				// configuration de la tâche
				$frequency = Mage::getStoreConfig('cronlog/email/frequency');
				$weekly  = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
				$monthly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;

				// minute hour day-of-month month-of-year day-of-week (Dimanche = 0, Lundi = 1...)
				// 15	 01   1			*			 *		   => monthly
				// 15	 01   *			*			 0|1		 => weekly
				// 15	 01   *			*			 *		   => daily
				if ($frequency === $monthly)
					$config->setValue('15 01 1 * *');
				else if ($frequency === $weekly)
					$config->setValue('15 01 * * '.Mage::getStoreConfig('general/locale/firstday'));
				else
					$config->setValue('15 01 * * *');

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
	// = révision : 7
	// » Renvoie une période : hier, la semaine dernière ou le mois dernier
	// » Doit être appelé pour le calcul des dates de la veille uniquement
	private function getDateRange($range) {

		$dateStart = Mage::app()->getLocale()->date();
		$dateEnd = Mage::app()->getLocale()->date();

		$dateStart->setHour(0);
		$dateStart->setMinute(0);
		$dateStart->setSecond(0);

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
			$dateStart->subDay(date('t', mktime(0,0,0, date('n') - 1)));
			$dateEnd->subDay(1);
		}

		$dateStart->setTimezone('Etc/UTC');
		$dateEnd->setTimezone('Etc/UTC');

		return array(
			'from' => $dateStart, 'to' => $dateEnd, 'datetime' => true,
			'fromTime' => $dateStart->getTimestamp(), 'toTime' => $dateEnd->getTimestamp()
		);
	}
}