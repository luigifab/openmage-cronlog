<?php
/**
 * Created W/29/02/2012
 * Updated S/09/11/2019
 *
 * Copyright 2012-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/magento/cronlog
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

	public function _(string $data, $a = null, $b = null) {
		return (mb_stripos($txt = $this->__(' '.$data, $a, $b), ' ') === 0) ? $this->__($data, $a, $b) : $txt;
	}

	public function escapeEntities($data, bool $quotes = false) {
		return htmlspecialchars($data, $quotes ? ENT_SUBSTITUTE | ENT_COMPAT : ENT_SUBSTITUTE | ENT_NOQUOTES);
	}

	public function formatDate($date = null, $format = Zend_Date::DATETIME_LONG, $showTime = false) {
		$object = Mage::getSingleton('core/locale');
		return str_replace($object->date($date)->toString(Zend_Date::TIMEZONE), '', $object->date($date)->toString($format));
	}

	public function getHumanDuration($start, $end) {

		if (!in_array($start, ['', '0000-00-00 00:00:00', null]) && !in_array($end, ['', '0000-00-00 00:00:00', null])) {

			$data    = is_numeric($start) ? $start : strtotime($end) - strtotime($start);
			$minutes = (int) ($data / 60);
			$seconds = $data % 60;

			if ($data > 599)
				$data = '<strong>'.(($seconds > 9) ? $minutes.':'.$seconds : $minutes.':0'.$seconds).'</strong>';
			else if ($data > 59)
				$data = '<strong>'.(($seconds > 9) ? '0'.$minutes.':'.$seconds : '0'.$minutes.':0'.$seconds).'</strong>';
			else if ($data > 1)
				$data = ($seconds > 9) ? '00:'.$data : '00:0'.$data;
			else
				$data = '⩽&nbsp;1';

			return $data;
		}
	}

	public function getNumberToHumanSize(int $number) {

		if ($number < 1) {
			return '';
		}
		else if (($number / 1024) < 1024) {
			$size = $number / 1024;
			$size = Zend_Locale_Format::toNumber($size, ['precision' => 2]);
			return $this->__('%s kB', str_replace(['.00', ',00'], '', $size));
		}
		else if (($number / 1024 / 1024) < 1024) {
			$size = $number / 1024 / 1024;
			$size = Zend_Locale_Format::toNumber($size, ['precision' => 2]);
			return $this->__('%s MB', str_replace(['.00', ',00'], '', $size));
		}
		else {
			$size = $number / 1024 / 1024 / 1024;
			$size = Zend_Locale_Format::toNumber($size, ['precision' => 2]);
			return $this->__('%s GB', str_replace(['.00', ',00'], '', $size));
		}
	}
}