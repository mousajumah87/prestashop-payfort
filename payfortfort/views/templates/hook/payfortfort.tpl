        <style>
        p.payment_module a:hover {
            background-color: #f6f6f6 !important;
        }
        </style>
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <p class="payment_module" >	
                    <input name="x_invoice_num" type="hidden" value="{$x_invoice_num}">
                    <p class="payment_module">
                        <a onclick="$('#payfortpaymentform input[type=submit]').click();" title="{l s='Pay with PayFort' mod='payfortfort'}" style="display: block;text-decoration: none; cursor:pointer; font-weight: bold;background:url(modules/payfortfort/img/cc.png) 15px 15px no-repeat #fbfbfb;clear:both">
                            Pay With Debit/Cradit Card		
                        </a>
                    </p>
                </p>
            </div>
            {$payfort_form}
        </div>
    </div>
</div>
</form>
