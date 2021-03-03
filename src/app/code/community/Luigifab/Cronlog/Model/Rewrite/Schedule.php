<?php
/**
 * Created S/16/05/2015
 * Updated D/06/09/2019
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

class Luigifab_Cronlog_Model_Rewrite_Schedule extends Mage_Cron_Model_Schedule {

	public function setExecutedAt($date) {

		Mage::unregister('current_cron');
		Mage::register('current_cron', $this);

		$this->setData('status', self::STATUS_RUNNING);
		$this->setData('executed_at', $date);

		return $this;
	}
}