<tr>
    <td><input type="text" name="rewrite[]" value="{data:rule?data:rule:''}"></td>
    <td><input type="text" name="rewrite-redirect[]" value="{data:redirect?data:redirect:''}"></td>
    <td><select name="rewrite-options[]">
            <option value="L"{data:option=='L'?' selected':''}>[L]</option>
            <option value="R,L"{data:option=='R,L'?' selected':''}>[R,L]</option>
            <option value="R=301,L"{data:option=='R=301,L'?' selected':''}>[R=301,L]</option>
            <option value="NC,L"{data:option=='NC,L'?' selected':''}>[NC,L]</option>
            <option value="NC,R,L"{data:option=='NC,R,L'?' selected':''}>[NC,R,L]</option>
            <option value="NC,R=301,L"{data:option=='NC,R=301,L'?' selected':''}>[NC,R=301,L]</option>
        </select></td>
    <td class="align-right"><a href="#" class="button red" title="Remove Rule" onclick="return removeRewriteRule(this);">x</a></td>
</tr>
