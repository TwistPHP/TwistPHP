<div>
	<strong>{data:device_name==''?'Untitled':data:device_name}{data:current==true?' (current)':''}</strong>
	<small>Last Login {prettytime[data:last_login]}</small>
	<a href="?forget-device={data:device}">forget</a>
	<a href="?edit-device={data:device}">edit</a>
</div>