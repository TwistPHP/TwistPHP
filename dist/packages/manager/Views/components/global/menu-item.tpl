<li{(route:uri=='/'&&data:uri=='')||(route:uri^=data:uri&&data:uri!='')?' class="current"':''}>
    <i class="fa fa-fw {data:icon}"></i><a href="{route:base_uri}{data:uri}" title="{data:name}">{data:name}</a>
    {(route:uri=='/'&&data:uri=='')||(route:uri^=data:uri&&data:uri!='')?data:submenu:''}
</li>
