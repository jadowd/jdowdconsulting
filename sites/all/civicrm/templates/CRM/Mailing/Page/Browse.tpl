{if $action eq 8 or $action eq 64} 
<fieldset><legend>{if $action eq 8}{ts}Delete Mailing{/ts}{else}{ts}Cancel Mailing{/ts}{/if}</legend>
<div class=status>{if $action eq 8}{ts 1=$subject}Are you sure you want to delete the mailing '%1'?{/ts}{else}{ts 1=$subject}Are you sure you want to cancel the mailing '%1'?{/ts}{/if}</div>
<dl><dt></dt><dd>{$form.buttons.html}</dd></dl>
</fieldset>
{/if}
<div class="action-link">
    <a accesskey="N" href="{crmURL p='civicrm/mailing/send' q='reset=1'}" class="button"><span>&raquo; {ts}New Mailing{/ts}</span></a><br/><br/>
</div>
{include file="CRM/Mailing/Form/Search.tpl"}

{if $rows}
{include file="CRM/common/pager.tpl" location="top"}
{include file="CRM/common/pagerAToZ.tpl}

{strip}
<table>
  <tr class="columnheader">
  {foreach from=$columnHeaders item=header}
    <th>
      {if $header.sort}
        {assign var='key' value=$header.sort}
        {$sort->_response.$key.link}
      {else}
        {$header.name}
      {/if}
    </th>
  {/foreach}
  </tr>

  {counter start=0 skip=1 print=false}
  {foreach from=$rows item=row}
  <tr class="{cycle values="odd-row,even-row"}">
    <td>{$row.name}</td>
    <td>{$row.status}</td>
    <td>{$row.scheduled}</td>
    <td>{$row.start}</td>
    <td>{$row.end}</td>
    <td>{$row.action}</td>
  </tr>
  {/foreach}
</table>
{/strip}

{include file="CRM/common/pager.tpl" location="bottom"}
    <div class="action-link">
        <a accesskey="N" href="{crmURL p='civicrm/mailing/send' q='reset=1'}" class="button"><span>&raquo; {ts}New Mailing{/ts}</span></a><br/>
    </div>
{* No mailings to list. Check isSearch flag to see if we're in a search or not. *}
{elseif $isSearch eq 1}
    <div class="status messages">
        <dl>
            <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"/></dt>
            {capture assign=browseURL}{crmURL p='civicrm/mailing/browse' q="reset=1"}{/capture}
            <dd>
               {ts}No Sent Mailings match your search criteria. Suggestions:{/ts} 
                <div class="spacer"></div>
                <ul>
                <li>{ts}Check your spelling.{/ts}</li>
                <li>{ts}Try a different spelling or use fewer letters.{/ts}</li>
                </ul>
                {ts 1=$browseURL}Or you can <a href='%1'>browse all Sent Mailings</a>.{/ts}
            </dd>
        </dl>
    </div>
{elseif $unscheduled}
    <div class="messages status">
        <dl>
            <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt>
            {capture assign=crmURL}{crmURL p='civicrm/mailing/send' q='reset=1'}{/capture}
            <dd>{ts 1=$crmURL}There are no Unscheduled Mailings. You can <a href='%1'>create and send one</a>.{/ts}</dd>
        </dl>
   </div>
{else}
    <div class="messages status">
        <dl>
            <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt>
            {capture assign=crmURL}{crmURL p='civicrm/mailing/send' q='reset=1'}{/capture}
            <dd>{ts 1=$crmURL}There are no Scheduled or Sent Mailings. You can <a href='%1'>create and send one</a>.{/ts}</dd>
        </dl>
   </div>
{/if}
