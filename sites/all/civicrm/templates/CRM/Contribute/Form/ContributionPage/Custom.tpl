{* WizardHeader.tpl provides visual display of steps thru the wizard as well as title for current step *}
{include file="CRM/common/WizardHeader.tpl"}
<div id="help">
    <p>{ts}You may want to collect information from contributors beyond what is required to make a contribution. For example, you may want to inquire about volunteer availability and skills. Add any number of fields to your contribution form by selecting CiviCRM Profiles (collections of fields) to include at the beginning of the page, and/or at the bottom.{/ts}</p>
    {capture assign=crmURL}{crmURL p='civicrm/admin/uf/group' q="reset=1&action=browse"}{/capture}
    <p>{ts 1=$crmURL}You can use existing CiviCRM Profiles on your page - OR create profile(s) specifically for use in Online Contribution pages. Go to <a href='%1'>Administer CiviCRM Profiles</a> if you need to review, modify or create profiles (you can come back at any time to select or update the Profile(s) used for this page).{/ts}</p>
</div>
 
<div class="form-item">
    <fieldset><legend>{ts}Include Profiles{/ts}</legend>
    <dl>
    <dt>{$form.custom_pre_id.label}</dt><dd>{$form.custom_pre_id.html}</dd>
    <dt>&nbsp;</dt><dd class="description">{ts}Include additional fields in this online contribution page by configuring and selecting a CiviCRM Profile to be included at the top of the page (immediately after the introductory message).{/ts}{help id="contrib-profile"}</dd>
    <dt>{$form.custom_post_id.label}</dt><dd>{$form.custom_post_id.html}</dd>
    <dt>&nbsp;</dt><dd class="description">{ts}Include additional fields in this online contribution page by configuring and selecting a CiviCRM Profile to be included at the bottom of the page.{/ts}</dd>
    </dl>
    <div id="crm-submit-buttons">
        <dl><dt></dt><dd>{$form.buttons.html}</dd></dl>  
    </div>
    </fieldset>
</div>
