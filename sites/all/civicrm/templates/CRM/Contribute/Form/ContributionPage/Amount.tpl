{* WizardHeader.tpl provides visual display of steps thru the wizard as well as title for current step *}
{include file="CRM/common/WizardHeader.tpl"}

{capture assign=docURLTitle}{ts}Opens online documentation in a new window.{/ts}{/capture}

<div id="help">
    {ts 1="http://wiki.civicrm.org/confluence//x/LCQ" 2=$docURLTitle}Use this form to configure Contribution Amount options. You can give contributors the ability to enter their own contribution amounts - and/or provide a fixed list of amounts. For fixed amounts, you can enter a label for each 'level' of contribution (e.g. Friend, Sustainer, etc.). If you allow people to enter their own dollar amounts, you can also set minimum and maximum values. Depending on your choice of Payment Processor, you may be able to offer a recurring contribution option (<a href='%1' target='_blank' title='%2'>more info...</a>).{/ts}
</div>
 
<div class="form-item">
    <fieldset><legend>{ts}Contribution Amounts{/ts}</legend>
    {if !$paymentProcessor}
        {capture assign=ppUrl}{crmURL p='civicrm/admin/paymentProcessor' q="reset=1"}{/capture}
        <div class="status message">
                {ts 1=$ppUrl 2=$docURLTitle 3="http://wiki.civicrm.org/confluence//x/ihk"}No Payment Processor has been configured / enabled for your site. Unless you are only using CiviContribute to solicit non-monetary / in-kind contributions, you will need to <a href='%1'>configure a Payment Processor</a>. Then return to this screen and assign the processor to this Contribution Page. (<a href='%3' target='_blank' title='%2'>read more...</a>){/ts}
        </div>
    {/if}
    <table class="form-layout-compressed">  
        <tr><th scope="row" class="label" width="20%">{$form.is_monetary.label}</th>
            <td>{$form.is_monetary.html}<br />
            <span class="description">{ts}Uncheck this box if you are using this contribution page for free membership signup ONLY, or to solicit in-kind / non-monetary donations such as furniture, equipment.. etc.{/ts}</span></td>
        </tr>
        {if $paymentProcessor}
        <tr><th scope="row" class="label" width="20%">{$form.payment_processor_id.label}</th>
            <td>{$form.payment_processor_id.html}<br />
            <span class="description">{ts 1="http://wiki.civicrm.org/confluence//x/ihk" 2=$docURLTitle}Select the payment processor to be used for contributions submitted from this contribution page (unless you are soliciting non-monetary / in-kind contributions only). (<a href='%1' target='_blank' title='%2'>read more...</a>){/ts}</span></td>
        </tr>
        {/if}
        <tr><th scope="row" class="label">{$form.amount_block_is_active.label}</th>
            <td>{$form.amount_block_is_active.html}<br />
            <span class="description">{ts}Uncheck this box if you are using this contribution page for membership signup and renewal only - and you do NOT want users to select or enter any additional contribution amounts.{/ts}</span></td>
        </tr>
            <tr><th scope="row" class="label">{$form.is_pay_later.label}</th>
            <td>{$form.is_pay_later.html}<br />
            <span class="description">{ts}Check this box if you want to give users the option to submit payment offline (e.g. mail in a check, call in a credit card, etc.).{/ts}</span></td></tr>
        <tr id="payLaterFields"><td>&nbsp;</td>
            <td>
            <table class="form-layout">
                <tr><th scope="row" class="label">{$form.pay_later_text.label} <span class="marker" title="This field is required.">*</span></th>
                <td>{$form.pay_later_text.html|crmReplace:class:big}<br />
                    <span class="description">{ts}Text displayed next to the checkbox for the 'pay later' option on the contribution form.{/ts}</span></td></tr> 
                <tr><th scope="row" class="label">{$form.pay_later_receipt.label} <span class="marker" title="This field is required.">*</span></th>
                <td>{$form.pay_later_receipt.html|crmReplace:class:big}<br />
                    <span class="description">{ts}Instructions added to Confirmation and Thank-you pages, as well as the confirmation email, when the user selects the 'pay later' option (e.g. 'Mail your check to ... within 3 business days.').{/ts}</span></td></tr>
            </table>
            </td>
        </tr>
    </table>

    <div id="amountFields">
        <table class="form-layout-compressed">
            {* handle CiviPledge fields *} 
            {if $civiPledge}
            <tr><th scope="row" class="label" width="20%"">{$form.is_pledge_active.label}</th>
                <td>{$form.is_pledge_active.html}<br />
                    <span class="description">{ts}Check this box if you want to give users the option to make a Pledge (a commitment to contribute a fixed amount on a recurring basis).{/ts}</span>
                </td>
            </tr>
            <tr id="pledgeFields"><td></td><td>
                <table class="form-layout-compressed">
                    <tr><th scope="row" class="label">{$form.pledge_frequency_unit.label}<span class="marker"> *</span></th>
                        <td>{$form.pledge_frequency_unit.html}<br />
                            <span class="description">{ts}Which frequencies can the user pick from (e.g. every 'week', every 'month', every 'year')?{/ts}</span></td>
                    </tr>
                    <tr><th scope="row" class="label">{$form.is_pledge_interval.label}</th>
                        <td>{$form.is_pledge_interval.html}<br />
                            <span class="description">{ts}Can they also set an interval (e.g. every '3' months)?{/ts}</span></td>
                    </tr>
                    <tr><th scope="row" class="label">{$form.initial_reminder_day.label}</th>
                        <td>{$form.initial_reminder_day.html}
                            <span class="label">{ts}days prior to each scheduled payment due date.{/ts}</span></td>
                    </tr>
                    <tr><th scope="row" class="label">{$form.max_reminders.label}</th>
                        <td>{$form.max_reminders.html}
                            <span class="label">{ts}reminders for each scheduled payment.{/ts}</span></td>
                    </tr>
                    <tr><th scope="row" class="label">{$form.additional_reminder_day.label}</th>
                        <td>{$form.additional_reminder_day.html}	
                            <span class="label">{ts}days after the last one sent, up to the maximum number of reminders.{/ts}</span></td>
                    </tr>
                </table>
                </td>
            </tr>
            {/if}

            {if $form.is_recur}
            <tr><th scope="row" class="label" width="20%">{$form.is_recur.label}</th>
               <td>{$form.is_recur.html}<br />
                  <span class="description">{ts}Check this box if you want to give users the option to make recurring contributions. (This feature requires that you use 'PayPal Website Standard' OR 'PayJunction' as your payment processor.){/ts}</span>
               </td>
            </tr>
            <tr id="recurFields"><td>&nbsp;</td>
               <td>
                  <table class="form-layout-compressed">
                    <tr><th scope="row" class="label">{$form.recur_frequency_unit.label}<span class="marker" title="This field is required.">*</span></th>
                        <td>{$form.recur_frequency_unit.html}<br />
                        <span class="description">{ts}Select recurring units supported for recurring payments.{/ts}</span></td>
                    </tr> 
                    <tr><th scope="row" class="label">{$form.is_recur_interval.label}</th>
                        <td>{$form.is_recur_interval.html}<br />
                        <span class="description">{ts}Can users also set an interval (e.g. every '3' months)?{/ts}</span></td>
                    </tr>
                  </table>
                </td>
            </tr>
            {/if}    
	
            <tr><th scope="row" class="label" width="20%">{$form.is_allow_other_amount.label}</th>
            <td>{$form.is_allow_other_amount.html}<br />
            <span class="description">{ts}Check this box if you want to give users the option to enter their own contribution amount. Your page will then include a text field labeled <strong>Other Amount</strong>.{/ts}</span></td></tr>

            <tr id="minMaxFields"><td>&nbsp;</td><td>
               <table class="form-layout-compressed">
                <tr><th scope="row" class="label">{$form.min_amount.label}</th>
                <td>{$form.min_amount.html|crmMoney}</td></tr> 
                <tr><th scope="row" class="label">{$form.max_amount.label}</th>
                <td>{$form.max_amount.html|crmMoney}<br />
                <span class="description">{ts 1=5|crmMoney}If you have chosen to <strong>Allow Other Amounts</strong>, you can use the fields above to control minimum and/or maximum acceptable values (e.g. don't allow contribution amounts less than %1).{/ts}</span></td></tr>
               </table>
            </td></tr>
            
            <tr><td colspan="2">
                <fieldset><legend>{ts}Fixed Contribution Options{/ts}</legend>
                    {ts}Use the table below to enter up to ten fixed contribution amounts. These will be presented as a list of radio button options. Both the label and dollar amount will be displayed.{/ts}<br />
                    <table id="map-field-table">
                        <tr class="columnheader" ><th scope="column">{ts}Contribution Label{/ts}</th><th scope="column">{ts}Amount{/ts}</th><th scope="column">{ts}Default?{/ts}<br />(&nbsp;<a href="#" title="unselect" onclick="unselectRadio('default', 'Amount'); return false;" >unselect</a>&nbsp;)</th></tr>
                        {section name=loop start=1 loop=11}
                            {assign var=idx value=$smarty.section.loop.index}
                            <tr><td class="even-row">{$form.label.$idx.html}</td><td>{$form.value.$idx.html|crmMoney}</td><td class="even-row">{$form.default.$idx.html}</td></tr>
                        {/section}
                    </table>
              </fieldset>
            </td></tr>
        </table>
      </div>
	
      <div id="crm-submit-buttons">
        <dl><dt></dt><dd> {$form.buttons.html}<br></dd></dl>
      </div>
    </fieldset>
</div>

{literal}
<script type="text/javascript">
	var element_other_amount = document.getElementsByName('is_allow_other_amount');
  	if (! element_other_amount[0].checked) {
	   hide('minMaxFields', 'table-row');
	}
	var amount_block = document.getElementsByName('amount_block_is_active');
  	if ( ! amount_block[0].checked) {
	   hide('amountFields');
        }
	var pay_later = document.getElementsByName('is_pay_later');
  	if ( ! pay_later[0].checked) {
	    hide('payLaterFields', 'table-row');
        }
	
	function minMax(chkbox) {
           if (chkbox.checked) {
	     show('minMaxFields', 'table-row');
 	   } else {
		 hide('minMaxFields');
		 document.getElementById("min_amount").value = '';
		 document.getElementById("max_amount").value = '';
	  }
	}
	
	function amountBlock(chkbox) {
           if (chkbox.checked) {
	       show('amountFields', 'block');
           } else {
	       hide('amountFields', 'block');
           }
        }
	
	function payLater(chkbox) {
           if (chkbox.checked) {
	       show('payLaterFields',  'table-row');
	   } else {
	       hide('payLaterFields',  'table-row');
	   }
        }
</script>
{/literal}
{if $form.is_recur}
{include file="CRM/common/showHideByFieldValue.tpl" 
    trigger_field_id    ="is_recur"
    trigger_value       ="true"
    target_element_id   ="recurFields" 
    target_element_type ="table-row"
    field_type          ="radio"
    invert              = "false"
}
{/if}
{if $civiPledge}
{include file="CRM/common/showHideByFieldValue.tpl" 
    trigger_field_id    = "is_pledge_active"
    trigger_value       = "true"
    target_element_id   = "pledgeFields" 
    target_element_type = "table-row"
    field_type          = "radio"
    invert              = "false"
}
{/if}

