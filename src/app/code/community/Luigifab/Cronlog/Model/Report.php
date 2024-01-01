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

class Luigifab_Cronlog_Model_Report extends Luigifab_Cronlog_Model_Observer {

	// CRON cronlog_send_report
	public function send($cron = null, bool $preview = false) {

		$frequency = Mage::getStoreConfig('cronlog/email/frequency');
		$oldLocale = Mage::getSingleton('core/translate')->getLocale();
		$newLocale = Mage::app()->getStore()->isAdmin() ? $oldLocale : Mage::getStoreConfig('general/locale/code');
		$locales   = [];

		// charge les tâches cron
		if ($frequency == Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY)
			$dates = $this->getDateRange('month');
		else if ($frequency == Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY)
			$dates = $this->getDateRange('week');
		else
			$dates = $this->getDateRange('day');

		$jobs = Mage::getResourceModel('cron/schedule_collection')
			->addFieldToFilter('created_at', [
				'datetime' => true,
				'from' => $dates['start']->toString(Zend_Date::RFC_3339),
				'to'   => $dates['end']->toString(Zend_Date::RFC_3339),
			])
			->setOrder('schedule_id', 'desc');

		// search locales and emails
		$data = Mage::getStoreConfig('cronlog/email/recipient_email');
		if ($preview) {
			$locales = [$oldLocale => ['hack@example.org']];
		}
		else if (!empty($data) && ($data != 'a:0:{}')) {
			if (str_contains($data, '{')) {
				$data = @unserialize($data, ['allowed_classes' => false]);
				if (!empty($data)) {
					foreach ($data as $datum) {
						if (!in_array($datum['email'], ['hello@example.org', 'hello@example.com', '']))
							$locales[empty($datum['locale']) ? $newLocale : $datum['locale']][] = $datum['email'];
					}
				}
			}
			else {
				// compatibility with previous version
				$data = array_filter(preg_split('#\s+#', $data));
				foreach ($data as $datum) {
					if (!in_array($datum, ['hello@example.org', 'hello@example.com', '']))
						$locales[$newLocale][] = $datum;
				}
			}
		}

		// generate and send the report
		foreach ($locales as $locale => $recipients) {

			if (!$preview)
				Mage::getSingleton('core/translate')->setLocale($locale)->init('adminhtml', true);

			$errors = [];
			foreach ($jobs as $job) {

				if (!in_array($job->getData('status'), ['error', 'missed']))
					continue;

				$errors[] = sprintf('(%d) %s / %s / %s %s',
					count($errors) + 1,
					'<a href="'.$this->getEmailUrl('adminhtml/cronlog_history/view', ['id' => $job->getId()]).'" style="font-weight:700; color:#E41101; text-decoration:none;">'.$this->__('Job %d: %s', $job->getId(), $job->getData('job_code')).'</a>',
					$this->_('Scheduled At: %s', $this->formatDate($job->getData('scheduled_at'))),
					$this->__('Status: %s (%s)', $this->__(ucfirst($job->getData('status'))), $job->getData('status')),
					'<pre lang="mul" style="margin:0.5em; font-size:0.9em; color:#767676; white-space:pre-wrap;">'.
						$job->getData('messages').
					'</pre>'
				);
			}

			if ($frequency == Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY)
				$text = $this->_('monthly');
			else if ($frequency == Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY)
				$text = $this->_('weekly');
			else
				$text = $this->_('daily');

			$html = $this->sendEmailToRecipients($locale, $recipients, [
				'frequency'        => $text,
				'date_period_from' => $dates['start']->toString(Zend_Date::DATETIME_FULL),
				'date_period_to'   => $dates['end']->toString(Zend_Date::DATETIME_FULL),
				'total_cron'       => count($jobs),
				'total_pending'    => count($jobs->getItemsByColumnValue('status', 'pending')),
				'total_running'    => count($jobs->getItemsByColumnValue('status', 'running')),
				'total_success'    => count($jobs->getItemsByColumnValue('status', 'success')),
				'total_missed'     => count($jobs->getItemsByColumnValue('status', 'missed')),
				'total_error'      => count($jobs->getItemsByColumnValue('status', 'error')),
				'list'             => implode('</li><li style="margin:0.8em 0 0.5em;">', $errors),
			], $preview);

			if ($preview)
				return $html;
		}

		Mage::getSingleton('core/translate')->setLocale($oldLocale)->init('adminhtml', true);

		if (is_object($cron))
			$cron->setData('messages', 'memory: '.((int) (memory_get_peak_usage(true) / 1024 / 1024)).'M (max: '.ini_get('memory_limit').')'."\n".print_r($locales, true));

		return $locales;
	}

	protected function getDateRange(string $range, int $coef = 1) {

		$dateStart = Mage::getSingleton('core/locale')->date()->setHour(0)->setMinute(0)->setSecond(0);
		$dateEnd   = Mage::getSingleton('core/locale')->date()->setHour(23)->setMinute(59)->setSecond(59);

		// de 1 (pour Lundi) à 7 (pour Dimanche)
		// permet d'obtenir des semaines du lundi au dimanche
		$day = $dateStart->toString(Zend_Date::WEEKDAY_8601) - 1;

		if ($range == 'month') {
			$dateStart->setDay(3)->subMonth($coef)->setDay(1);
			$dateEnd->setDay(3)->subMonth($coef)->setDay($dateEnd->toString(Zend_Date::MONTH_DAYS));
		}
		else if ($range == 'week') {
			$dateStart->subDay($day + 7 * $coef);
			$dateEnd->subDay($day + 7 * $coef - 6);
		}
		else if ($range == 'day') {
			$dateStart->subDay(1);
			$dateEnd->subDay(1);
		}

		return ['start' => $dateStart, 'end' => $dateEnd];
	}

	protected function getEmailUrl(string $url, array $params = []) {

		if (Mage::getStoreConfigFlag('web/seo/use_rewrites'))
			return preg_replace('#/[^/]+\.php\d*/#', '/', Mage::helper('adminhtml')->getUrl($url, $params));

		return preg_replace('#/[^/]+\.php(\d*)/#', '/index.php$1/', Mage::helper('adminhtml')->getUrl($url, $params));
	}

	protected function sendEmailToRecipients(string $locale, array $emails, array $vars = [], bool $preview = false) {

		$vars['config'] = $this->getEmailUrl('adminhtml/system/config');
		$vars['config'] = mb_substr($vars['config'], 0, mb_strrpos($vars['config'], '/system/config'));
		$sender = Mage::getStoreConfig('cronlog/email/sender_email_identity');

		foreach ($emails as $email) {

			$template = Mage::getModel('core/email_template');
			$template->setDesignConfig(['store' => null]);
			$template->loadDefault('cronlog_email_template', $locale);

			if ($preview)
				return $template->getProcessedTemplate($vars);

			$template->setSenderName(Mage::getStoreConfig('trans_email/ident_'.$sender.'/name'));
			$template->setSenderEmail(Mage::getStoreConfig('trans_email/ident_'.$sender.'/email'));
			$template->setSentSuccess($template->send($email, null, $vars));
			//exit($template->getProcessedTemplate($vars));

			if (!$template->getSentSuccess())
				Mage::throwException($this->__('Can not send the report by email to %s.', $email));
		}

		return true;
	}
}