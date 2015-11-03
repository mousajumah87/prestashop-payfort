<?php

/*
 * 2007-2013 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_'))
    exit;

class PayfortFORT extends PaymentModule {

    public function __construct() {
        
        $this->name = 'payfortfort';
        $this->tab = 'payments_gateways';
        $this->version = '1.5.6';
        $this->author = 'PayFort';
        $this->fort_available_currencies = array('USD', 'AUD', 'CAD', 'EUR', 'GBP', 'NZD');

        parent::__construct();

        $this->displayName = 'Payfort FORT Gateway';
        $this->description = $this->l('Receive payment with Credit or Debit Card');


        /* For 1.4.3 and less compatibility */
        $updateConfig = array(
            'PS_OS_CHEQUE' => 1,
            'PS_OS_PAYMENT' => 2,
            'PS_OS_PREPARATION' => 3,
            'PS_OS_SHIPPING' => 4,
            'PS_OS_DELIVERED' => 5,
            'PS_OS_CANCELED' => 6,
            'PS_OS_REFUND' => 7,
            'PS_OS_ERROR' => 8,
            'PS_OS_OUTOFSTOCK' => 9,
            'PS_OS_BANKWIRE' => 10,
            'PS_OS_PAYPAL' => 11,
            'PS_OS_WS_PAYMENT' => 12);

        foreach ($updateConfig as $u => $v)
            if (!Configuration::get($u) || (int) Configuration::get($u) < 1) {
                if (defined('_' . $u . '_') && (int) constant('_' . $u . '_') > 0)
                    Configuration::updateValue($u, constant('_' . $u . '_'));
                else
                    Configuration::updateValue($u, $v);
            }

        /* Check if cURL is enabled */
        if (!is_callable('curl_exec'))
            $this->warning = $this->l('cURL extension must be enabled on your server to use this module.');

        /* Backward compatibility */
        require(_PS_MODULE_DIR_ . $this->name . '/backward_compatibility/backward.php');
       
    }

    public function install() {
        return parent::install() &&
                $this->registerHook('orderConfirmation') &&
                $this->registerHook('payment') &&
                $this->registerHook('header') &&
                $this->registerHook('backOfficeHeader') &&
                Configuration::updateValue('PAYFORT_FORT_SANDBOX_MODE', 1) &&
                Configuration::updateValue('PAYFORT_FORT_LANGUAGE', 'en') &&
                Configuration::updateValue('PAYFORT_FORT_COMMAND', 'AUTHORIZATION') &&
                Configuration::updateValue('PAYFORT_HASH_ALGORITHM', 'SHA1') &&
                Configuration::updateValue('PAYFORT_FORT_HOLD_REVIEW_OS', _PS_OS_ERROR_);
                
    }

    public function uninstall() {
        Configuration::deleteByName('PAYFORT_FORT_SANDBOX_MODE');
        Configuration::deleteByName('PAYFORT_FORT_LANGUAGE');
        Configuration::deleteByName('PAYFORT_FORT_MERCHANT_IDENTIFIER');
        Configuration::deleteByName('PAYFORT_FORT_ACCESS_CODE');
        Configuration::deleteByName('PAYFORT_FORT_COMMAND');
        Configuration::deleteByName('PAYFORT_SHA_ALGORITHM');
        Configuration::deleteByName('PAYFORT_REQUEST_SHA_PHRASE');
        Configuration::deleteByName('PAYFORT_RESPONSE_SHA_PHRASE');
        Configuration::deleteByName('PAYFORT_FORT_HOLD_REVIEW_OS');

        /* Removing credentials configuration variables */
        $currencies = Currency::getCurrencies(false, true);
        foreach ($currencies as $currency)
            if (in_array($currency['iso_code'], $this->fort_available_currencies)) {
                Configuration::deleteByName('PAYFORT_FORT_LOGIN_ID_' . $currency['iso_code']);
                Configuration::deleteByName('PAYFORT_FORT_KEY_' . $currency['iso_code']);
            }

        return parent::uninstall();
    }

    public function hookOrderConfirmation($params) {
        if ($params['objOrder']->module != $this->name)
            return;

        if ($params['objOrder']->getCurrentState() != Configuration::get('PS_OS_ERROR')) {
            Configuration::updateValue('PAYFORTFORT_CONFIGURATION_OK', true);
            $this->context->smarty->assign(array('status' => 'ok', 'id_order' => intval($params['objOrder']->id)));
        } else
            $this->context->smarty->assign('status', 'failed');

        return $this->display(__FILE__, 'views/templates/hook/orderconfirmation.tpl');
    }

    public function hookBackOfficeHeader() {
        $this->context->controller->addJQuery();
        if (version_compare(_PS_VERSION_, '1.5', '>='))
            $this->context->controller->addJqueryPlugin('fancybox');

        $this->context->controller->addJS($this->_path . 'js/payfortfort.js');
        $this->context->controller->addCSS($this->_path . 'css/payfortfort.css');
    }

    public function getContent() {
        $html = '';
        if (Tools::isSubmit('submitModule')) {
            $payfort_sandbox_mode = (int) Tools::getvalue('payfort_sandbox_mode');
            if ($payfort_sandbox_mode == 1) {
                Configuration::updateValue('PAYFORT_FORT_SANDBOX_MODE', 1);
            } else {
                Configuration::updateValue('PAYFORT_FORT_SANDBOX_MODE', 0);
            }
            $payfort_language = Tools::getvalue('payfort_language');
            if ($payfort_language == 'ar') {
                Configuration::updateValue('PAYFORT_FORT_LANGUAGE', 'ar');
            } else {
                Configuration::updateValue('PAYFORT_FORT_LANGUAGE', 'en');
            }
            $payfort_fort_command = Tools::getvalue('payfort_fort_command');
            if ($payfort_start_action == 'AUTHORIZATION') {
                Configuration::updateValue('PAYFORT_FORT_COMMAND', 'AUTHORIZATION');
            } else {
                Configuration::updateValue('PAYFORT_FORT_COMMAND', 'PURCHASE');
            }
            $payfort_fort_sha_algorithm = Tools::getvalue('payfort_fort_sha_algorithm');
            if ($payfort_fort_sha_algorithm == 'SHA1') {
                Configuration::updateValue('PAYFORT_FORT_SHA_ALGORITHM', 'SHA1');
            } else if ($payfort_fort_sha_algorithm == 'SHA256') {
                Configuration::updateValue('PAYFORT_FORT_SHA_ALGORITHM', 'SHA256');
            } else if ($payfort_fort_sha_algorithm == 'SHA512') {
                Configuration::updateValue('PAYFORT_FORT_SHA_ALGORITHM', 'SHA512');
            } else {
                Configuration::updateValue('PAYFORT_FORT_SHA_ALGORITHM', 'SHA1'); //default
            }
            foreach ($_POST as $key => $value) {
                if ($key != "tab" && $key != "submitModule") {
                    Configuration::updateValue(strtoupper($key), $value);
                }
            }
            $html .= $this->displayConfirmation($this->l('Configuration updated'));
        }
        // For "Hold for Review" order status
        $order_states = OrderState::getOrderStates((int) $this->context->cookie->id_lang);
        $this->context->smarty->assign(array(
            'available_currencies' => $this->fort_available_currencies,
            'currencies' => $currencies,
            'module_dir' => $this->_path,
            'order_states' => $order_states,
            'PAYFORT_FORT_SANDBOX_MODE' => Configuration::get('PAYFORT_FORT_SANDBOX_MODE'),
            'PAYFORT_FORT_HOLD_REVIEW_OS' => (int) Configuration::get('PAYFORT_FORT_HOLD_REVIEW_OS'),
            'PAYFORT_FORT_COMMAND' => Configuration::get('PAYFORT_FORT_COMMAND'),
            'PAYFORT_FORT_LANGUAGE' => Configuration::get('PAYFORT_FORT_LANGUAGE'),
            'PAYFORT_FORT_SHA_ALGORITHM' => Configuration::get('PAYFORT_FORT_SHA_ALGORITHM'),
        ));
        
        $configuration_merchant_identifier      = 'PAYFORT_FORT_MERCHANT_IDENTIFIER';
        $configuration_access_code              = 'PAYFORT_FORT_ACCESS_CODE';
        $configuration_request_sha_phrase       = 'PAYFORT_FORT_REQUEST_SHA_PHRASE';
        $configuration_response_sha_phrase      = 'PAYFORT_FORT_RESPONSE_SHA_PHRASE';

        $this->context->smarty->assign($configuration_merchant_identifier, Configuration::get($configuration_merchant_identifier));
        $this->context->smarty->assign($configuration_access_code, Configuration::get($configuration_access_code));
        $this->context->smarty->assign($configuration_request_sha_phrase, Configuration::get($configuration_request_sha_phrase));
        $this->context->smarty->assign($configuration_response_sha_phrase, Configuration::get($configuration_response_sha_phrase));
        $this->context->smarty->assign('host_to_host_url', _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php?fc=module&module=payfortfort&controller=payment');
        return $this->context->smarty->fetch(dirname(__FILE__) . '/views/templates/admin/configuration.tpl');
    }

    public function hookPayment($params) {
        $currency = Currency::getCurrencyInstance($this->context->cookie->id_currency);
        $isFailed = Tools::getValue('payfortforterror');

        $url = _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php?fc=module&module=payfortfort&controller=payment';
   
        $this->context->smarty->assign('url', $url);
        return $this->display(__FILE__, 'views/templates/hook/payfortfort.tpl');
    }
    
    public function hookHeader() {
        if (_PS_VERSION_ < '1.5')
            Tools::addJS(_PS_JS_DIR_ . 'jquery/jquery.validate.creditcard2-1.0.1.js');
        else
            $this->context->controller->addJqueryPlugin('validate-creditcard');
    }
}
