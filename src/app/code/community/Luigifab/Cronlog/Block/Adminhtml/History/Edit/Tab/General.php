<?php
/**
 * Created D/10/02/2013
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

use Mage_Adminhtml_Block_Widget_Tab_Interface as Mage_Adminhtml_BWT_Interface;
class Luigifab_Cronlog_Block_Adminhtml_History_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_BWT_Interface {

	public function getTabLabel() {
		return $this->__('Job');
	}

	public function getTabTitle() {
		return null;
	}

	public function isHidden() {
		return false;
	}

	public function canShowTab() {
		return true;
	}


	protected function _prepareForm() {

		// formulaire
		$form = new Varien_Data_Form();
		$this->setForm($form);

		$fieldset = $form->addFieldset('general_form', [
			'legend'   => $this->__('Job')
		]);

		$fieldset->addField('job_code', 'select', [
			'label'    => $this->__('Code'),
			'name'     => 'job_code',
			'class'    => 'required-entry',
			'required' => true,
			'values'   => Mage::getSingleton('cronlog/source_jobs')->toOptionArray()
		]);

		$fieldset->addField('scheduled_at', 'select', [
			'label'    => $this->__('Scheduled into'),
			'name'     => 'scheduled_at',
			'class'    => 'required-entry',
			'required' => true,
			'values'   => [
				['value' => 1,  'label' => $this->__('%d minute', 1)],
				['value' => 5,  'label' => $this->__('%d minutes', 5)],
				['value' => 10, 'label' => $this->__('%d minutes', 10)],
				['value' => 15, 'label' => $this->__('%d minutes', 15)],
				['value' => 30, 'label' => $this->__('%d minutes', 30)],
				['value' => 45, 'label' => $this->__('%d minutes', 45)],
				['value' => 60, 'label' => $this->__('%d minutes', 60)]
			]
		]);

		// sélection par défaut
		$session = Mage::getSingleton('adminhtml/session')->getFormData();

		if (is_array($session) && !empty($session['job_code']) && !empty($session['scheduled_at']))
			$form->setValues($session);
		else if (!empty($this->getRequest()->getParam('code')))
			$form->setValues(['job_code' => $this->getRequest()->getParam('code')]);

		return parent::_prepareForm();
	}
}