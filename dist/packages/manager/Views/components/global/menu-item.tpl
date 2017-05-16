<li{(route:uri=='/'&&data:uri=='')||(route:uri^=data:uri&&data:uri!='')?' class="current"':''}>
    <a href="{route:base_uri}{data:uri}" title="{data:name}">{data:name}<i class="fa fa-fw {data:icon}"></i></a>
    {(route:uri=='/'&&data:uri=='')||(route:uri^=data:uri&&data:uri!='')?data:submenu:''}
</li>