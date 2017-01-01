<?php
/**
 * Created W/29/02/2012
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

class Luigifab_Cronlog_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getVersion() {
		return (string) Mage::getConfig()->getModuleConfig('Luigifab_Cronlog')->version;
	}

	public function _($data, $a = null, $b = null) {
		return (strpos($txt = $this->__(' '.$data, $a, $b), ' ') === 0) ? $this->__($data, $a, $b) : $txt;
	}

	public function getNumberToHumanSize($number) {

		if ($number < 1) {
			return '';
		}
		else if (($number / 1024) < 1024) {
			$size = $number / 1024;
			$size = Zend_Locale_Format::toNumber($size, array('precision' => 2));
			return $this->__('%s KB', str_replace(array('.00',',00'), '', $size));
		}
		else if (($number / 1024 / 1024) < 1024) {
			$size = $number / 1024 / 1024;
			$size = Zend_Locale_Format::toNumber($size, array('precision' => 2));
			return $this->__('%s MB', str_replace(array('.00',',00'), '', $size));
		}
		else {
			$size = $number / 1024 / 1024 / 1024;
			$size = Zend_Locale_Format::toNumber($size, array('precision' => 2));
			return $this->__('%s GB', str_replace(array('.00',',00'), '', $size));
		}
	}

	public function getHumanDuration($job) {

		if (!in_array($job->getData('executed_at'), array('', '0000-00-00 00:00:00', null)) &&
		    !in_array($job->getData('finished_at'), array('', '0000-00-00 00:00:00', null))) {

			$data = strtotime($job->getData('finished_at')) - strtotime($job->getData('executed_at'));
			$minutes = intval($data / 60);
			$seconds = intval($data % 60);

			if ($data > 599)
				$data = '<strong>'.(($seconds > 9) ? $minutes.':'.$seconds : $minutes.':0'.$seconds).'</strong>';
			else if ($data > 59)
				$data = '<strong>'.(($seconds > 9) ? '0'.$minutes.':'.$seconds : '0'.$minutes.':0'.$seconds).'</strong>';
			else if ($data > 1)
				$data = ($seconds > 9) ? '00:'.$data : '00:0'.$data;
			else
				$data = '⩽ 1';

			return $data;
		}
	}
}