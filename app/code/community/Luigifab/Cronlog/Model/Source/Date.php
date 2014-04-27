<?php
/**
 * Created W/13/02/2013
 * Updated W/13/02/2013
 * Version 1
 *
 * Copyright 2013-2014 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Cronlog_Model_Source_Date {

	public function toOptionArray() {

		return array(
			array('label' => Mage::helper('cronlog')->__('%d minute', 1), 'value' => '1'),
			array('label' => Mage::helper('cronlog')->__('%d minutes', 2), 'value' => '2'),
			array('label' => Mage::helper('cronlog')->__('%d minutes', 3), 'value' => '3'),
			array('label' => Mage::helper('cronlog')->__('%d minutes', 4), 'value' => '4'),
			array('label' => Mage::helper('cronlog')->__('%d minutes', 5), 'value' => '5'),
			array('label' => Mage::helper('cronlog')->__('%d minutes', 10), 'value' => '10'),
			array('label' => Mage::helper('cronlog')->__('%d minutes', 15), 'value' => '15'),
			array('label' => Mage::helper('cronlog')->__('%d minutes', 20), 'value' => '20'),
			array('label' => Mage::helper('cronlog')->__('%d minutes', 30), 'value' => '30'),
			array('label' => Mage::helper('cronlog')->__('%d minutes', 45), 'value' => '45'),
			array('label' => Mage::helper('cronlog')->__('%d minutes', 60), 'value' => '60')
		);
	}
}