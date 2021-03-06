{*
 * $Revision: 17617 $
 * Read this before changing templates!  http://codex.gallery2.org/Gallery2:Editing_Templates
 *}
<div class="gbBlock gcBackground1">
  <h2> {g->text text="Digibug Photo Printing Settings"} </h2>
</div>

{if isset($status.saved)}
<div class="gbBlock"><h2 class="giSuccess">
  {g->text text="Settings saved successfully"}
</h2></div>
{/if}

<div class="gbBlock">
  <p class="giDescription">
  {g->text text="Sell your photos as prints or printed gift products!"}<br />
  <input type="radio" id="rbDigibugGalleryId" name="{g->formVar var="form[digibugIdChoice]"}" 
    value="gallery" {if $form.digibugIdChoice == 'gallery'}checked="checked"{/if} 
    onclick="setCustom(0)" />
  <label for="rbDigibugGalleryId">{g->text text="Simple mode."}</label><br />
  {g->text text="A percentage of the proceeds of sales are donated to the Gallery project. Thank you for your support!"}
  </p>
  <p class="giDescription">
  <input type="radio" id="rbDigibugCustomerId" name="{g->formVar var="form[digibugIdChoice]"}" 
    value="owner" {if $form.digibugIdChoice == 'owner'}checked="checked"{/if} 
    onclick="setCustom(1)" />
  <label for="rbDigibugCustomerId">{g->text text="Advanced mode."}</label><br />
  {g->text text="Visit the %sDigibug configuration page%s for information on registering for a Digibug Pro Photographer account, and to learn how to obtain your Digibug Company ID and Event ID.  For general information, please refer to the %sDigibug.com website.%s"
    arg1="<a href=\"http://www.digibug.com/redirects/digibugapi_how_to.php\">" arg2="</a>"
    arg3="<a href=\"http://www.digibug.com/\">" arg4="</a>"}
  </p>
  
  <script type="text/javascript">
    // <![CDATA[
    var formCustomerId = '{$form.digibugCustomerId}';
    var formPricelistId = '{$form.digibugPricelistId}';
    {literal}
    function setCustom(val) {
      var customerId = document.getElementById('formDigibugCustomerId');
      var pricelistId = document.getElementById('formDigibugEventId');
      if (val == 0) {
      	customerId.value = '';
	customerId.disabled = true;
	pricelistId.value = '';
	pricelistId.disabled = true;
      } else {
      	customerId.value = formCustomerId;
	customerId.disabled = false;
	pricelistId.value = formPricelistId;
	pricelistId.disabled = false;
      }
    }
    {/literal}
    // ]]>
  </script>
  
  <table class="gbDataTable">
    <tr><td>
      <label for="formDigibugCustomerId">
	{g->text text="Digibug Company ID"}
      </label>
    </td><td>
      <input type="text" size="6" id="formDigibugCustomerId" autocomplete="off"
       name="{g->formVar var="form[digibugCustomerId]"}"
       value={if $form.digibugIdChoice == 'owner'}"{$form.digibugCustomerId}"
       	     {else}"" disabled="disabled"{/if}/>
    </td></tr>
    {if isset($form.error.digibugCustomerId.invalid)}
    <tr><td colspan="2">
      <div class="giError">
	{g->text text="You must enter a valid digibug customer id."}
      </div>
    </td></tr>
    {/if}
    <tr><td>
      <label for="formDigibugEventId">
	{g->text text="Event ID"}
      </label>
    </td><td>
      <input type="text" size="6" id="formDigibugEventId" autocomplete="off"
       name="{g->formVar var="form[digibugPricelistId]"}" 
       value={if $form.digibugIdChoice == 'owner'}"{$form.digibugPricelistId}"
             {else}"" disabled="disabled"{/if}/>
    </td></tr>
    {if isset($form.error.digibugPricelistId.invalid)}
    <tr><td colspan="2">
      <div class="giError">
	{g->text text="Please create an event or use your default event id as your pricelist id."}
      </div>
    </td></tr>
    {/if}
  </table>
</div>

<div class="gbBlock gcBackground1">
  <input type="submit" class="inputTypeSubmit"
   name="{g->formVar var="form[action][save]"}" value="{g->text text="Save"}"/>
  {if $AdminDigibug.isConfigure}
    <input type="submit" class="inputTypeSubmit"
     name="{g->formVar var="form[action][cancel]"}" value="{g->text text="Cancel"}"/>
  {else}
    <input type="submit" class="inputTypeSubmit"
     name="{g->formVar var="form[action][reset]"}" value="{g->text text="Reset"}"/>
  {/if}
</div>
