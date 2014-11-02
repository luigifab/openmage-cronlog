<?php
/**
 * Created W/13/02/2013
 * Updated D/31/08/2014
 * Version 5
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

class Luigifab_Cronlog_Model_Source_Date extends Luigifab_Cronlog_Helper_Data {

	public function toOptionArray() {

		return array(
			array('value' => 1,  'label' => $this->__('%d minute', 1)),
			array('value' => 2,  'label' => $this->__('%d minutes', 2)),
			array('value' => 3,  'label' => $this->__('%d minutes', 3)),
			array('value' => 4,  'label' => $this->__('%d minutes', 4)),
			array('value' => 5,  'label' => $this->__('%d minutes', 5)),
			array('value' => 10, 'label' => $this->__('%d minutes', 10)),
			array('value' => 15, 'label' => $this->__('%d minutes', 15)),
			array('value' => 20, 'label' => $this->__('%d minutes', 20)),
			array('value' => 30, 'label' => $this->__('%d minutes', 30)),
			array('value' => 45, 'label' => $this->__('%d minutes', 45)),
			array('value' => 60, 'label' => $this->__('%d minutes', 60))
		);
	}
}