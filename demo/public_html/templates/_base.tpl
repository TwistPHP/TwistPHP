<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    {template:common/head.tpl}
    <body>
    <h1>TwistPHP Demo Site</h1>
    <ul>
        <li><a href="/">Home</a></li>
        <li><a href="/count">Countdown</a></li>
        <li><a href="/examples">Examples</a></li>
        <li><a href="/twitter">Twitter</a></li>
    </ul>
    {route:response}
    <p>You requested {route:request} which used the item {route:response_item} with the array {route:data}</p>
    </body>
</html>