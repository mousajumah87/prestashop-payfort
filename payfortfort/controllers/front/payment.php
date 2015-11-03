<?php
/*
* 2007-2014 PrestaShop
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
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since 1.5.0
 */
class PayfortfortPaymentModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	 */
          
	public function postProcess()
	{

        if (isset($_POST['form'])){
            //set as pending order
            $cart = $this->context->cart;

            $customer = new Customer($cart->id_customer);
            if (!Validate::isLoadedObject($customer))
                Tools::redirect('index.php?controller=order&step=1');

            $currency = $this->context->currency;

            $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
            $mailVars = array();

            
            $invoiceAddress = new Address((int) $cart->id_address_invoice);
            $amount = number_format((float) $cart->getOrderTotal(true, 3), 2, '.', '');
            $amount_in_cents = $amount * 100;

            $this->module->validateOrder($cart->id, 3, $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
            
            $url = _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php?fc=module&module=payfortfort&controller=payment';
            
            $sandbox_mode = Configuration::get('PAYFORT_FORT_SANDBOX_MODE');
            
            if ($sandbox_mode){
                $gateway_url = 'https://sbcheckout.payfort.com/FortAPI/paymentPage';
            }
            else{
                $gateway_url = 'https://checkout.payfort.com/FortAPI/paymentPage';
            }
            
            $objOrder = new Order($this->module->currentOrder);
            
            $post_data = array(
                'amount'                => $amount_in_cents,
                'currency'              => strtoupper($currency->iso_code),
                'merchant_identifier'   => Configuration::get('PAYFORT_FORT_MERCHANT_IDENTIFIER'),
                'access_code'           => Configuration::get('PAYFORT_FORT_ACCESS_CODE'),
                'merchant_reference'    => $this->module->currentOrder,
                //'merchant_reference'    => $objOrder->reference,
                'customer_email'        => $customer->email,
                'command'               => Configuration::get('PAYFORT_FORT_COMMAND'),
                'language'              => Configuration::get('PAYFORT_FORT_LANGUAGE'),
                'return_url'            => $url
            );
            
            
            if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0)
                Tools::redirect('index.php?controller=order&step=1');
            
            // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
            $authorized = false;
            foreach (Module::getPaymentModules() as $module)
            if ($module['name'] == 'payfortfort')
            {
                $authorized = true;
                break;
            }
            if (!$authorized)
                die($this->module->l('This payment method is not available.', 'validation'));

            //calculate request signature
            $sha_string = '';
            ksort($post_data);
            foreach ($post_data as $k=>$v){
                $sha_string .= "$k=$v";
            }

            $sha_string = Configuration::get('PAYFORT_FORT_REQUEST_SHA_PHRASE') . $sha_string . Configuration::get('PAYFORT_FORT_REQUEST_SHA_PHRASE');
            $signature = hash(Configuration::get('PAYFORT_FORT_SHA_ALGORITHM') ,$sha_string);
            
            $form =  '<form style="display:none" name="payfortpaymentform" id="payfortpaymentform" method="post" action="'.$gateway_url.'">';
            
            foreach ($post_data as $k => $v){
                $form .= '<input type="hidden" name="'.$k.'" value="'.$v.'">';
            }
            
            $form .= '<input type="hidden" name="signature" value="'.$signature.'">';
            $form .= '<input type="submit" value="" id="submit" name="submit2">';

            echo '<html>';
            echo '<head><script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>';
            echo '</head>';
            echo '<body>';
            echo 'Redirecting to PayFort ....';
            echo $form;
            echo '</body>';
            echo '<script>$(document).ready(function(){$("#payfortpaymentform input[type=submit]").click();})</script>';
            echo '</html>';
            die();
        }
        
        elseif (isset($_GET['response_code']) && isset($_GET['merchant_reference'])){
            
            $success = false;
            $params = $_GET;
            $hash_string = '';
            $signature = $_GET['signature'];
            
            unset($params['signature']);
            unset($params['fc']);
            unset($params['module']);
            unset($params['controller']);
            ksort($params);
            
            foreach ($params as $k=>$v){
                if ($v != ''){
                    $hash_string .= strtolower($k).'='.$v;
                }
            }

            $hash_string = Configuration::get('PAYFORT_FORT_RESPONSE_SHA_PHRASE') . $hash_string . Configuration::get('PAYFORT_FORT_RESPONSE_SHA_PHRASE');
            $true_signature = hash(Configuration::get('PAYFORT_FORT_SHA_ALGORITHM') ,$hash_string);
            
            if ($true_signature != $signature){
                $success = false;
            }
            else{
                $response_code      = $params['response_code'];
                $response_message   = $params['response_message'];
                $status             = $params['status'];
                
                if (substr($response_code, 2) != '000'){
                    $success = false;
                }
                else{
                    $success = true;
                    $cart = $this->context->cart;

                    // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
                    $authorized = false;
                    foreach (Module::getPaymentModules() as $module)
                        if ($module['name'] == 'payfortfort')
                        {
                            $authorized = true;
                            break;
                        }
                    if (!$authorized)
                        die($this->module->l('This payment method is not available.', 'validation'));

                    $customer = new Customer($cart->id_customer);

                    if (!Validate::isLoadedObject($customer))
                        Tools::redirect('index.php?controller=order&step=1');
                    
                    $mailVars = array();

                    $objOrder = new Order($params['merchant_reference']);
                    $history = new OrderHistory();
                    $history->id_order = (int)$objOrder->id;
                    $history->changeIdOrderState(Configuration::get('PAYFORT_FORT_HOLD_REVIEW_OS'), (int)($objOrder->id));

                    Tools::redirect('index.php?controller=order-confirmation&id_cart='.$objOrder->id_cart.'&id_module='.$this->module->id.'&id_order='.$objOrder->id.'&key='.$customer->secure_key);
                }
            }
            
            if (!$success){
                $objOrder = new Order($params['merchant_reference']);
                $history = new OrderHistory();
                $history->id_order = (int)$objOrder->id;
                $history->changeIdOrderState(8, (int)($objOrder->id)); //order status=3
                Tools::redirect('index.php');
            }
            
        }
        

	}
}
