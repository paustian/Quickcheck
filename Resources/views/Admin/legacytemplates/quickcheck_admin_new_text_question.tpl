{*  $Id: quickcheck_admin_new.htm 19361 2006-07-03 14:57:57Z timpaustian $  *}
{include file="Admin/quickcheck_admin_menu.tpl"}
<div class="z-adminbox">
<h3>{gt text="Create a new text question"}</h3>
<p>{gt text="Fill in the text areas below with the question, its answer and any explanation necessary. Note that these questions are not graded by the module."}</p>
<form action="{modurl modname="quickcheck" type="admin" func="createTextQuestion"}" method="post"
      enctype="multipart/form-data">
      {if isset($id)}
      <input type="hidden" name="id" value="{$id}" />
    {/if}
    <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />         
           <p>{gt text="Enter the question"}: </p>
    <p><textarea name="q_text" id="q_text" wrap="soft" rows="3" cols="60">
{if isset($q_text)}
{$q_text}
{/if}</textarea></p>
    <p>{gt text="Enter the correct answer"}: </p>
    <p><textarea name="q_answer" id="q_answer" wrap="soft" rows="3" cols="60">
{if isset($q_answer)}
{$q_answer}
{/if}</textarea></p>
    <p>{gt text="Write the explanation for the correct answer:"} </p>
    <textarea name="q_explan" id="q_explan" wrap="soft" rows="3" cols="60">
{if isset($q_explan)}
{$q_explan}
{/if}</textarea>
    <div class="pn-formrow">
        <label for="pages_categories">{gt text="Category"}</label>
        { gt text="Choose Category" }
        {nocache}
        <ul id="pages_categories" class="selector_category">
            {foreach from=$catregistry key=property item=category}
            {if isset($selectedValue)}
            <li>{selector_category category=$category name="quickcheck_quest[__CATEGORIES__][$property]" field="id" selectedValue=$selectedValue defaultValue="0"}</li>
            {else}
            <li>{selector_category category=$category name="quickcheck_quest[__CATEGORIES__][$property]" field="id" defaultValue="0"}</li>
            {/if}
            {/foreach}
        </ul>
        {/nocache}
    </div>
    <div class="z-buttons">
    {button src=button_ok.gif set=icons/small alt="_CREATE" title="Update" value="update"}
    {button src=button_cancel.gif set=icons/small alt="_CANCEL" title="Cancel" value="cancel"}
    </div>
</form>
</div>