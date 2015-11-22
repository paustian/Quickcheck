{*  $Id: quickcheck_admin_findunanswered.htm 16293 2010-07-04 11:23:32Z timpaustian $  *}
{include file="quickcheck_admin_menu.htm"}
<form action="{modurl modname="quickcheck" type="admin" func="modifydeletequestions"}" method="post">
      <p>Total unanswered is: {$count}</p>
    <table border>
        <tr>
            <th>{gt text='Question'}</th>
            <th>{gt text='Answer'}</th>
            <th>{gt text='Explanation'}</th>
            <th>{gt text='Edit Question'}</th>
        </tr>
        {section loop=$questions name="i"}
        <tr>
            <td>{$questions[i].q_text}</td>
            <td>{section loop=$questions[i].q_answer name="j"}
                {$questions[i].q_answer[j]}<br />
                {/section}
            </td>
            <td>{$questions[i].q_explan}</td>
            <td>{button src=edit.gif set=icons/extrasmall alt="Edit" title="Edit" value="edit_`$questions[i].id`"}</td>
        </tr>
        {/section}
    </table>
</form>