<?php
/**
 * Created D/31/08/2014
 * Updated S/09/12/2023
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

class Luigifab_Cronlog_Model_Source_Lifetime {

	protected $_options;

	public function toOptionArray() {

		if (empty($this->_options)) {
			$helper = Mage::helper('cronlog');
			$this->_options = [
				['value' => 5 * 24 * 60,  'label' => $helper->__('%d days', 5)], // 5+
				['value' => 7 * 24 * 60,  'label' => $helper->__('%d days', 7)], // 5+
				['value' => 14 * 24 * 60, 'label' => $helper->_('%d days (%d weeks)',  14, 2)], // 2-4
				['value' => 28 * 24 * 60, 'label' => $helper->_('%d days (%d weeks)',  28, 4)], // 2-4
				// translate.php                           ->__('%d days (%d weeks)')           // 5+
				['value' => 31 * 24 * 60, 'label' => $helper->__('%d days (%d month)', 31, 1)], // 1
				['value' => 62 * 24 * 60, 'label' => $helper->_('%d days (%d months)', 62, 2)], // 2-4
				['value' => 93 * 24 * 60, 'label' => $helper->_('%d days (%d months)', 93, 3)], // 2-4
				// translate.php                           ->__('%d days (%d months)')          // 5+
			];
		}

		return $this->_options;
	}
}