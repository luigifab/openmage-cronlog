<?php
/**
 * Created S/31/05/2014
 * Updated M/20/08/2019
 *
 * Copyright 2012-2022 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Cronlog_Block_Adminhtml_Manage extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {

		parent::__construct();

		$this->_controller = 'adminhtml_manage';
		$this->_blockGroup = 'cronlog';
		$this->_headerText = $this->__('Manage cron jobs');

		$this->_removeButton('add');

		$this->_addButton('back', [
			'label'   => $this->__('Back'),
			'onclick' => "setLocation('".$this->getUrl('*/cronlog_history/index')."');",
			'class'   => 'back'
		]);
	}
}