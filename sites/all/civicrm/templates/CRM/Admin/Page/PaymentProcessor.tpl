{capture assign=docURLTitle}{ts}Opens online documentation in a new window.{/ts}{/capture}
<div id="help">
    {ts 1="http://wiki.civicrm.org/confluence//x/ihk" 2=$docURLTitle}You can configure one or more Payment Processors for your CiviCRM installation. You must then assign an active Payment Processor to each <strong>Online Contribution Page</strong> and each paid <strong>Event</strong> (<a href='%1' target='_blank' title='%2'>read more...</a>).{/ts}
</div>

{if $action eq 1 or $action eq 2 or $action eq 8}
   {include file="CRM/Admin/Form/PaymentProcessor.tpl"}
{/if}

{if $rows}
<div id="ltype">
        {strip}
        <table class="selector">
        <tr class="columnheader">
            <th >{ts}Name{/ts}</th>
            <th >{ts}Processor Type{/ts}</th>
            <th >{ts}Description{/ts}</th>
            <th >{ts}Enabled?{/ts}</th>
	    <th >{ts}Default?{/ts}</th>
            <th ></th>
        </tr>
        {foreach from=$rows item=row}
        <tr class="{cycle values="odd-row,even-row"} {$row.class}{if NOT $row.is_active} disabled{/if}">
	        <td>{$row.name}</td>	
	        <td>{$row.payment_processor_type}</td>	
            <td>{$row.description}</td>
	        <td>{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
            <td>{if $row.is_default eq 1}<img src="{$config->resourceBase}/i/check.gif" alt="{ts}Default{/ts}" />{/if}&nbsp;</td>
	        <td>{$row.action}</td>
        </tr>
        {/foreach}
        </table>
        {/strip}

        {if $action ne 1 and $action ne 2}
	    <div class="action-link">
    	<a href="{crmURL q="action=add&reset=1&pp=PayPal"}" id="newPaymentProcessor" class="button"><span>&raquo; {ts}New Payment Processor{/ts}</span></a>
        </div>
        {/if}
</div>
{elseif $action ne 1}
    <div class="messages status">
    <dl>
        <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"/></dt>
        {capture assign=crmURL}{crmURL p='civicrm/admin/paymentProcessor' q="action=add&reset=1&pp=PayPal"}{/capture}
        <dd>{ts 1=$crmURL}There are no Payment Processors entered. You can <a href='%1'>add one</a>.{/ts}</dd>
        </dl>
    </div>    
{/if}
