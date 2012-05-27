<?php
/**
 * Created J/17/05/2012
 * Updated D/27/05/2012
 * Version 5
 *
 * Copyright 2012 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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
	// = révision : 5
	// » Génère le rapport en fonction de la configuration (quotidien/hebdomadaire/mensuel)
	// » Envoi le rapport via un email transactionnel
	public function sendMail() {

		$frequency = Mage::getStoreConfig('cronlog/email/frequency');
		$locale = Mage::getSingleton('core/locale');
		$helper = Mage::helper('compressor');

		$daily = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY;
		$weekly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
		$monthly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;

		$texts = array($daily => $helper->__('daily'), $weekly => $helper->__('weekly'), $monthly => $helper->__('monthly'));
		$stats = array();

		// récupération des dates
		// ici on travaille avec les dates UTC
		if ($frequency === $daily) {
			$from = date('Y-m-d H:i:s', strtotime('yesterday'));
			$to = date('Y-m-d H:i:s', strtotime('yesterday') + 86399);

			//echo '<p>',$from,' » ',$to;
			//$from = $locale->date($from, Zend_Date::ISO_8601, null, false);
			//$to = $locale->date($to, Zend_Date::ISO_8601, null, false);
			//echo '<br />',$from,' » ',$to,'</p>';
		}
		else if ($frequency === $weekly) {
			$from = date('Y-m-d H:i:s', strtotime('last monday'));
			$to = date('Y-m-d H:i:s', strtotime('yesterday') + 86399);

			//echo '<p>',$from,' » ',$to;
			//$from = $locale->date($from, Zend_Date::ISO_8601, null, false);
			//$to = $locale->date($to, Zend_Date::ISO_8601, null, false);
			//echo '<br />',$from,' » ',$to,'</p>';
		}
		else if ($frequency === $monthly) {
			$from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m')-1, 1, date('y')));
			$to = date('Y-m-t H:i:s', mktime(0, 0, 0, date('m')-1, 1, date('y')) + 86399);

			//echo '<p>',$from,' » ',$to;
			//$from = $locale->date($from, Zend_Date::ISO_8601, null, false);
			//$to = $locale->date($to, Zend_Date::ISO_8601, null, false);
			//echo '<br />',$from,' » ',$to,'</p>';
		}

		// chargement des tâches cron de la période
		// ici on travaille avec les dates UTC en prenant soin d'y soustraire le décalage horaire de la locale
		// si maintenant à +2.00 on est Lundi 00h00, à +0.00 il est encore Dimanche 22h00
		$offset = Mage::getModel('core/date')->timestamp(time()) - time();

		$collection = Mage::getResourceModel('cron/schedule_collection');
		$collection->addFieldToFilter('created_at', array('gt' => date('c', strtotime($from) - $offset)));
		$collection->addFieldToFilter('created_at', array('lt' => date('c', strtotime($to) - $offset)));

		foreach ($collection as $job) {

			if (!isset($stats[$job->getStatus()]))
				$stats[$job->getStatus()] = array();

			$stats[$job->getStatus()][] = $job->getScheduleId();
		}

		// envoie de l'email
		// sendTransactional($templateId, $sender, $recipient, $name, $vars = array(), $storeId = null)
		// ici on travaille avec les dates UTC même si à l'affichage les dates seront par rapport à la locale
		$from = $locale->date($from, Zend_Date::ISO_8601, null, false);
		$to = $locale->date($to, Zend_Date::ISO_8601, null, false);

		$from = substr($from, 0, strrpos($from, ' '));
		$to = substr($to, 0, strrpos($to, ' '));

		$email = Mage::getModel('core/email_template');
		$email->sendTransactional(
			Mage::getStoreConfig('cronlog/email/template'),
			Mage::getStoreConfig('cronlog/email/sender_email_identity'),
			Mage::getStoreConfig('cronlog/email/recipient_email'),
			null,
			array(
				'frequency'  => $texts[$frequency],
				'total_cron' => count($collection),
				'total_pending' => (isset($stats['pending'])) ? count($stats['pending']) : 0,
				'total_running' => (isset($stats['running'])) ? count($stats['running']) : 0,
				'total_success' => (isset($stats['success'])) ? count($stats['success']) : 0,
				'total_missed'  => (isset($stats['missed'])) ? count($stats['missed']) : 0,
				'total_error'   => (isset($stats['error'])) ? count($stats['error']) : 0,
				'config_url'  => Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit', array('section' => 'cronlog')),
				'date_period' => ($from !== $to) ? $helper->__('from <strong>%s</strong> to <strong>%s</strong> included', $from, $to) : $helper->__('from <strong>%s</strong>', $from)
			)
		);

		if (!$email->getSentSuccess())
			throw new Exception('Can not send cronlog mail report');
	}


	// #### Programmation de la tâche cron ########################################## public ### //
	// = révision : 5
	// » Quotidien : tous les jours à 01h15 (daily)
	// » Hebdomadaire : tous les lundi à 01h15 (weekly)
	// » Mensuel : chaque premier jour du mois à 01h15 (monthly)
	public function updateConfig() {

		try {
			$config = Mage::getModel('core/config_data');
			$config->load('crontab/jobs/cronlog_send_report/schedule/cron_expr', 'path');

			if (Mage::getStoreConfig('cronlog/email/enabled') === '1') {

				$frequency = Mage::getStoreConfig('cronlog/email/frequency');
				$weekly  = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
				$monthly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;

				// minute hour day-of-month month-of-year day-of-week (Dimanche = 0, Lundi = 1...)
				// 15     01   1            *             *           => monthly
				// 15     01   *            *             0|1         => weekly
				// 15     01   *            *             *           => daily
				if ($frequency === $monthly)
					$config->setValue('15 01 1 * *');
				else if ($frequency === $weekly)
					$config->setValue('15 01 * * '.Mage::getStoreConfig('general/locale/firstday'));
				else
					$config->setValue('15 01 * * *');

				$config->setPath('crontab/jobs/cronlog_send_report/schedule/cron_expr');
				$config->save();
			}
			else {
				$config->delete();
			}
		}
		catch (Exception $e) {
			throw new Exception('Unable to save the cron expression for cronlog.');
		}
	}
}