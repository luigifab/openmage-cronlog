<?php
/**
 * Created J/17/05/2012
 * Updated D/17/12/2023
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

class Luigifab_Cronlog_Model_Observer extends Luigifab_Cronlog_Helper_Data {

	// EVENT admin_system_config_changed_section_cronlog (adminhtml)
	public function updateConfig() {

		$config = Mage::getModel('core/config_data');
		$config->load('crontab/jobs/cronlog_send_report/schedule/cron_expr', 'path');

		if (Mage::getStoreConfigFlag('cronlog/email/enabled')) {

			// quotidien, tous les jours à 1h00 (quotidien/daily)
			// hebdomadaire, tous les lundi à 1h00 (hebdomadaire/weekly)
			// mensuel, chaque premier jour du mois à 1h00 (mensuel/monthly)
			$frequency = Mage::getStoreConfig('cronlog/email/frequency');

			// minute hour day-of-month month-of-year day-of-week (Dimanche = 0, Lundi = 1...)
			// 0      1    1            *             *           => monthly
			// 0      1    *            *             0|1         => weekly
			// 0      1    *            *             *           => daily
			if ($frequency == Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY)
				$config->setData('value', '0 1 1 * *');
			else if ($frequency == Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY)
				$config->setData('value', '0 1 * * '.Mage::getStoreConfig('general/locale/firstday'));
			else
				$config->setData('value', '0 1 * * *');

			$config->setData('path', 'crontab/jobs/cronlog_send_report/schedule/cron_expr');
			$config->save();

			// test email
			if (!empty(Mage::app()->getRequest()->getPost('cronlog_email_test')))
				Mage::getSingleton('cronlog/report')->send();
		}
		else {
			$config->delete();
		}

		Mage::getConfig()->reinit();
	}
}