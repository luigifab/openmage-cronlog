<?php
/**
 * Created D/10/02/2013
 * Updated M/27/02/2018
 *
 * Copyright 2012-2019 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/magento/cronlog
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

class Luigifab_Cronlog_Block_Adminhtml_History_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form {

	protected function _prepareForm() {

		// formulaire
		$form = new Varien_Data_Form();
		$this->setForm($form);

		$fieldset = $form->addFieldset('general_form', array(
			'legend'   => $this->__('Job')
		));

		$fieldset->addField('job_code', 'select', array(
			'label'    => $this->__('Code'),
			'name'     => 'job_code',
			'class'    => 'required-entry',
			'required' => true,
			'values'   => Mage::getSingleton('cronlog/source_jobs')->toOptionArray()
		));

		$fieldset->addField('scheduled_at', 'select', array(
			'label'    => $this->__('Scheduled into'),
			'name'     => 'scheduled_at',
			'class'    => 'required-entry',
			'required' => true,
			'values'   => array(
				array('value' => 1,  'label' => $this->__('%d minute', 1)),
				array('value' => 5,  'label' => $this->__('%d minutes', 5)),
				array('value' => 10, 'label' => $this->__('%d minutes', 10)),
				array('value' => 15, 'label' => $this->__('%d minutes', 15)),
				array('value' => 30, 'label' => $this->__('%d minutes', 30)),
				array('value' => 45, 'label' => $this->__('%d minutes', 45)),
				array('value' => 60, 'label' => $this->__('%d minutes', 60))
			)
		));

		// sélection par défaut
		$session = Mage::getSingleton('adminhtml/session')->getFormData();

		if (is_array($session) && !empty($session['job_code']) && !empty($session['scheduled_at']))
			$form->setValues($session);
		else if (!empty($this->getRequest()->getParam('code')))
			$form->setValues(array('job_code' => $this->getRequest()->getParam('code')));

		return parent::_prepareForm();
	}
}