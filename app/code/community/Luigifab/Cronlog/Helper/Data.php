<?php
/**
 * Created W/29/02/2012
 * Updated S/26/04/2014
 * Version 2
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

class Luigifab_Cronlog_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getVersion() {
		return (string) Mage::getConfig()->getModuleConfig('Luigifab_Cronlog')->version;
	}

	public function getDateToUtc($date) {
		$dt = new DateTime($date, new DateTimeZone(Mage::app()->getStore()->getConfig('general/locale/timezone')));
		$dt->setTimezone(new DateTimeZone('UTC'));
		return $dt->format('Y-m-d H:i:s');
	}

	public function getDateToLocal($date) {
		$dt = new DateTime($date, new DateTimeZone('UTC'));
		$dt->setTimezone(new DateTimeZone(Mage::app()->getStore()->getConfig('general/locale/timezone')));
		return $dt->format('Y-m-d H:i:s');
	}
}