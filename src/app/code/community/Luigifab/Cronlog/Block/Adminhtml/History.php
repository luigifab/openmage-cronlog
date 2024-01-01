<?php
/**
 * Created W/29/02/2012
 * Updated D/11/12/2022
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

class Luigifab_Cronlog_Block_Adminhtml_History extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {

		parent::__construct();

		$this->_controller = 'adminhtml_history';
		$this->_blockGroup = 'cronlog';
		$this->_headerText = $this->__('Cron jobs');

		$this->_updateButton('add', 'label', $this->__('Add'));

		$this->_addButton('config', [
			'label'   => $this->__('Manage cron jobs'),
			'onclick' => "setLocation('".$this->getUrl('*/cronlog_manage/index')."');",
			'class'   => 'go',
		]);
	}
}