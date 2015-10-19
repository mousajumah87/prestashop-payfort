<?php

if (!defined('_PS_VERSION_'))
	exit;

// object module ($this) available
function upgrade_module_1_4_11($object)
{
	$upgrade_version = '1.4.11';

	$object->upgrade_detail[$upgrade_version] = array();

	// Updating variables name for environment and mode
	if ((bool)Configuration::get('PAYFORT_FORT_DEMO'))
		Configuration::updateValue('PAYFORT_FORT_SANDBOX', 1);
	else
		Configuration::updateValue('PAYFORT_FORT_SANDBOX', 0);

	Configuration::updateValue('PAYFORT_FORT_TEST_MODE', 0);
	Configuration::deleteByName('PAYFORT_FORT_DEMO');

	Configuration::updateValue('PAYFORT_FORT', $upgrade_version);
	return true;
}
