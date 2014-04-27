<?php
/**
 * Created W/28/03/2012
 * Updated S/26/04/2014
 * Version 1
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

class Luigifab_Cronlog_Block_Adminhtml_Widget_Datetime extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Datetime {

	public function render(Varien_Object $row) {
		return ($this->_getValue($row) != '0000-00-00 00:00:00') ? parent::render($row) : '';
	}
}