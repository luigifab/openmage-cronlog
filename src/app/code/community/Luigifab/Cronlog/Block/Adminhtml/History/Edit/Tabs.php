<?php
/**
 * Created D/10/02/2013
 * Updated M/05/02/2019
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

class Luigifab_Cronlog_Block_Adminhtml_History_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

	public function __construct() {

		parent::__construct();

		$this->setId('cronlogTabs');
		$this->setTitle($this->__('Job'));
		$this->setDestElementId('edit_form');
	}
}