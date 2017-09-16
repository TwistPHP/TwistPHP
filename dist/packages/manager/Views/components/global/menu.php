<?php

	$arrMenu = $arrMenuKey = array();
	$arrMenuItems = json_decode(file_get_contents(sprintf('%smanager/menu.json',TWIST_FRAMEWORK_DATA)),true);

	//Build the main menu array
	foreach($arrMenuItems as $arrEachMenuItem){
		$arrMenu[$arrEachMenuItem['order']*100] = $arrEachMenuItem;
		$arrMenuKey[$arrEachMenuItem['name']] = ($arrEachMenuItem['order']*100);
	}

	//Load in all the backend route hooks
	$arrMenuHooks = \Twist::framework()->hooks()->getAll('TWIST_MANAGER_MENU');

	if(count($arrMenuHooks)){

		foreach($arrMenuHooks as $strEachHook){
			$arrExtraItems = json_decode($strEachHook,true);

			foreach($arrExtraItems as $arrEachExtraItem){

				//if the menu item already exists then update/extend it
				if(array_key_exists($arrEachExtraItem['name'],$arrMenuKey)){
					$intOrder = $arrMenuKey[$arrEachExtraItem['name']];

					//Update the URI if required
					if(array_key_exists('uri',$arrEachExtraItem)){
						$arrMenu[$intOrder]['uri'] = $arrEachExtraItem['uri'];
					}

					//Update the icon if required
					if(array_key_exists('icon',$arrEachExtraItem)){
						$arrMenu[$intOrder]['icon'] = $arrEachExtraItem['icon'];
					}

					//Add additional sub-sections if required
					if(array_key_exists('sub-sections',$arrEachExtraItem)){

						if(count($arrMenu[$intOrder]['sub-sections'])){
							$arrMenu[$intOrder]['sub-sections'] = array_merge($arrMenu[$intOrder]['sub-sections'],$arrEachExtraItem['sub-sections']);
						}else{
							$arrMenu[$intOrder]['sub-sections'] = $arrEachExtraItem['sub-sections'];
						}
					}

				}else{
					//This is a new item, add it to the menu
					$intOrder = ($arrEachExtraItem['order']*100);

					while(array_key_exists($intOrder,$arrMenu)){
						$intOrder++;
					}

					$arrMenu[$intOrder] = $arrEachExtraItem;
					$arrMenuKey[$arrEachExtraItem['name']] = $intOrder;
				}
			}
		}
	}

	ksort($arrMenu);

	$strMenu = '';

	//Now build the actual menu
	foreach($arrMenu as $arrEachItem){

		$arrEachItem['uri'] = (substr($arrEachItem['uri'],0,1) == '/' || $arrEachItem['uri'] == '') ? $arrEachItem['uri'] : '/'.$arrEachItem['uri'];

		$arrEachItem['submenu'] = '';
		$arrSubMenu = array('submenu-items' => '');

		//If there are sub menu items then process them
		if(array_key_exists('sub-sections',$arrEachItem) && count($arrEachItem['sub-sections'])){
			foreach($arrEachItem['sub-sections'] as $arrEachSubItem){

				$arrEachSubItem['uri'] = (substr($arrEachSubItem['uri'],0,1) == '/' || $arrEachSubItem['uri'] == '') ? $arrEachSubItem['uri'] : '/'.$arrEachSubItem['uri'];
				$arrSubMenu['submenu-items'] .= \Twist::View()->build('./submenu-item.tpl',$arrEachSubItem);
			}

			$arrEachItem['submenu'] = \Twist::View()->build('./submenu.tpl',$arrSubMenu);
		}

		$strMenu .= \Twist::View()->build('./menu-item.tpl',$arrEachItem);
	}

	echo $strMenu;