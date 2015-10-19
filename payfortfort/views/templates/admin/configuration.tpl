<div class="payfortfort-wrapper">
<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post">
	<fieldset>
		<legend>{l s='Configure your Payfort FORT Payment Gateway' mod='payfortfort'}</legend>
				{assign var='configuration_merchant_identifier' value="PAYFORT_FORT_MERCHANT_IDENTIFIER"}
				{assign var='configuration_access_code' value="PAYFORT_FORT_ACCESS_CODE"}
                {assign var='configuration_request_sha_phrase' value="PAYFORT_FORT_REQUEST_SHA_PHRASE"}
				{assign var='configuration_response_sha_phrase' value="PAYFORT_FORT_RESPONSE_SHA_PHRASE"}
				<table>
					<tr>
						<td>
							<p>{l s='Credentials for' mod='payfortfort'}</p>
							<label for="PAYFORT_FORT_MERCHANT_IDENTIFIER">{l s='Merchant Identifier' mod='payfortfort'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="50" id="PAYFORT_FORT_MERCHANT_IDENTIFIER" name="PAYFORT_FORT_MERCHANT_IDENTIFIER" value="{$PAYFORT_FORT_MERCHANT_IDENTIFIER}" /></div>
                            
							<label for="PAYFORT_FORT_ACCESS_CODE">{l s='Access Code' mod='payfortfort'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="50" id="PAYFORT_FORT_ACCESS_CODE" name="PAYFORT_FORT_ACCESS_CODE" value="{$PAYFORT_FORT_ACCESS_CODE}" /></div>
                            
                            <label for="PAYFORT_FORT_REQUEST_SHA_PHRASE">{l s='Request SHA Phrase' mod='payfortfort'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="50" id="PAYFORT_FORT_REQUEST_SHA_PHRASE" name="PAYFORT_FORT_REQUEST_SHA_PHRASE" value="{$PAYFORT_FORT_REQUEST_SHA_PHRASE}" /></div>
                            
							<label for="PAYFORT_FORT_RESPONSE_SHA_PHRASE">{l s='Response SHA Phrase' mod='payfortfort'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="50" id="PAYFORT_FORT_RESPONSE_SHA_PHRASE" name="PAYFORT_FORT_RESPONSE_SHA_PHRASE" value="{$PAYFORT_FORT_RESPONSE_SHA_PHRASE}" /></div>
                        </td>
					</tr>
				</table><br />
				<hr size="1" style="background: #BBB; margin: 0; height: 1px;" noshade /><br />

		<label for="payfort_sandbox_mode"> {l s='Sandbox Mode:' mod='payfortfort'}</label>
		<div class="margin-form" id="payfortfort_sandbox_mode">
            <input type="radio" name="payfort_sandbox_mode" value="1" style="vertical-align: middle;" {if $PAYFORT_FORT_SANDBOX_MODE}checked="checked"{/if} />
			<span>{l s='Yes' mod='payfortfort'}</span><br/>
			<input type="radio" name="payfort_sandbox_mode" value="0" style="vertical-align: middle;" {if !$PAYFORT_FORT_SANDBOX_MODE}checked="checked"{/if} />
			<span>{l s='No' mod='payfortfort'}</span><br/>
		</div>
        <label for="payfort_fort_command">{l s='Command:' mod='payfortfort'}</label>
		<div class="margin-form" id="payfortfort_command">
			<input type="radio" name="payfort_fort_command" value="AUTHORIZATION" style="vertical-align: middle;" {if 'AUTHORIZATION' eq $PAYFORT_FORT_COMMAND}checked="checked"{/if} />
			<span>{l s='AUTHORIZATION' mod='payfortfort'}</span><br/>
			<input type="radio" name="payfort_fort_command" value="PURCHASE" style="vertical-align: middle;" {if 'PURCHASE' eq $PAYFORT_FORT_COMMAND}checked="checked"{/if} />
			<span>{l s='PURCHASE' mod='payfortfort'}</span><br/>
		</div>
        <label for="payfort_fort_sha_algorithm">{l s='SHA Algorithm' mod='payfortfort'}</label>
		<div class="margin-form">
			<select id="payfort_fort_sha_algorithm" name="payfort_fort_sha_algorithm">';
                <option value="SHA1" {if 'SHA1' eq $PAYFORT_FORT_HASH_ALGORITHM} selected {/if}>
                    SHA-1
                </option>
                <option value="SHA256" {if 'SHA256' eq $PAYFORT_FORT_HASH_ALGORITHM} selected {/if}>
                    SHA-256
                </option>
                <option value="SHA512" {if 'SHA512' eq $PAYFORT_FORT_HASH_ALGORITHM} selected {/if}>
                    SHA-512
                </option>
			</select>
		</div>
        <label for="payfort_fort_language">{l s='Language' mod='payfortfort'}</label>
		<div class="margin-form">
			<select id="payfort_fort_language" name="PAYFORT_FORT_LANGUAGE">';
                <option value="en" {if 'en' eq $PAYFORT_FORT_LANGUAGE} selected {/if}>
                    English (en)
                </option>
                <option value="ar" {if 'ar' eq $PAYFORT_FORT_LANGUAGE} selected {/if}>
                    Arabic (ar)
                </option>
			</select>
		</div>
		<label for="payfort_start_hold_review_os">{l s='Order status:  "Hold for Review" ' mod='payfortfort'}</label>
		<div class="margin-form">
			<select id="payfort_start_hold_review_os" name="PAYFORT_FORT_HOLD_REVIEW_OS">';
				// Hold for Review order state selection
				{foreach from=$order_states item='os'}
					<option value="{$os.id_order_state|intval}" {if $os.id_order_state|intval eq $PAYFORT_FORT_HOLD_REVIEW_OS} selected {/if}>
						{$os.name|stripslashes}
					</option>
				{/foreach}
			</select>
		</div>
        <label for="host_to_host_url">{l s='Host to Host URL: ' mod='payfortfort'}</label>
		<div class="margin-form">
			<input type="text" size="50" value="{$host_to_host_url}" readonly/>
		</div>
		<br />
		<center>
			<input type="submit" name="submitModule" value="{l s='Update settings' mod='payfortfort'}" class="button" />
		</center>
		<sub></sub>
	</fieldset>
</form>
</div>
