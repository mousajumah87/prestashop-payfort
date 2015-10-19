<?php

if (!defined('_PS_VERSION_'))
	exit;

// object module ($this) available
function upgrade_module_1_4_8($object)
{
	$upgrade_version = '1.4.8';

	$object->upgrade_detail[$upgrade_version] = array();

	// Variables name for Login and Key have now the currency
	if(Configuration::get('PAYFORT_FORT_LOGIN_ID') && Configuration::get('PAYFORT_FORT_KEY'))
	{
		$currencies = Currency::getCurrencies(false, true);
		foreach ($currencies as $currency)
		{
			if (in_array($currency['iso_code'], $object->start_available_currencies))
			{
				$configuration_id_name = 'PAYFORT_FORT_LOGIN_ID_'.$currency['iso_code'];
				$configuration_key_name = 'PAYFORT_FORT_KEY_'.$currency['iso_code'];

				Configuration::updateValue($configuration_id_name, Configuration::get('PAYFORT_FORT_LOGIN_ID'));
				Configuration::updateValue($configuration_key_name, Configuration::get('PAYFORT_FORT_KEY'));
			}
		}
	}

	Configuration::deleteByName('PAYFORT_FORT_LOGIN_ID');
	Configuration::deleteByName('PAYFORT_FORT_KEY');

	Configuration::updateValue('PAYFORT_FORT', $upgrade_version);
	return true;
}
