{include file="quickcheck_admin_menu.htm"}
<h3>{gt text="Modify the exam"}</h3>
<form action="{modurl modname="quickcheck" type="admin" func="update"}" method="post">
     <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Quickcheck"}" />
       <input type="hidden" name="exam_id" value="{$exam.id}" />
      <p>{gt text="Exam name"}: <input type="text" size="50" maxlength="255" name="name" value="{$exam.name}"> </p>
    <h3>{gt text="Exam questions"}</h3>
    <h4>{gt text="Current questions"}</h4>
    <table>
    {section loop=$curr_questions name=i}
    <tr>
        <td><input type="checkbox" value="{$curr_questions[i].id}" name="curr_questions[]" checked></td>
        <td>{$curr_questions[i].q_text}</td>
    </tr>
    {/section}
    </table>
    <h4>{gt text="Other questions"}</h4>
     <table>
        {assign var='curr_cat' value=''}
        {section loop=$other_questions name="i"}
        {if $curr_cat != $other_questions[i].cat_id}
        {assign var='curr_cat' value=$other_questions[i].cat_id}
        <tr>
            <td colspan="3"><b>{$other_questions[i].cat_name}</b></td>
        </tr>
        {/if}
        <tr>
            <td><input type="checkbox" value="{$other_questions[i].id}" name="other_questions[]"></td>
            <td>{$other_questions[i].q_text}</td>
        </tr>
        {/section}
</table>
{button src=button_ok.gif set=icons/small alt="_CREATE" title="_CREATE" value="create"} {gt text="Modify Exam"}
</form>

<ul id="treemenu2" class="treeview">
{section loop=category name=i}
<li>{$category[i].name}</li>
<ul>
    {section loop=category[i].questions name=j}
    <li></li>
    {/section}
</ul>
{/section}
</ul>