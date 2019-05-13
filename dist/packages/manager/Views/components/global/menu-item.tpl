<li{(route:uri=='/'&&data:uri=='')||(route:uri^=data:uri&&data:uri!='')?' class="current"':''}>
    <a href="{route:base_uri}{data:uri}" title="{data:name}"><i class="fas fa-fw {data:icon}"></i><span class="menu-text">{data:name}</span></a>
    {data:submenu}
</li>
