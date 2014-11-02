<?php
/**
 * Created W/29/02/2012
 * Updated D/01/06/2014
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

class Luigifab_Cronlog_Model_Source_Status {

	public function toOptionArray() {

		return array(
			array('value' => '', 'label' => '--'),
			array('value' => 'pending', 'label' => Mage::helper('cronlog')->__('Pending (pending)')),
			array('value' => 'running', 'label' => Mage::helper('cronlog')->__('Running (running)')),
			array('value' => 'success', 'label' => Mage::helper('cronlog')->__('Success (success)')),
			array('value' => 'missed',  'label' => Mage::helper('cronlog')->__('Missed (missed)')),
			array('value' => 'error',   'label' => Mage::helper('cronlog')->__('Error (error)'))
		);
	}
}