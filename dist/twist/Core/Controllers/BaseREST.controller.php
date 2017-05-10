<?php

/**
 * TwistPHP - An open source PHP MVC framework built from the ground up.
 * Copyright (C) 2016  Shadow Technologies Ltd.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
 * @license    https://www.gnu.org/licenses/gpl.html GPL License
 * @link       https://twistphp.com
 */

namespace Twist\Core\Controllers;

/**
 * An REST API base controller that can be used instead of Base when adding REST API support to your site. This controller should be used as an extension to a route controller class.
 * @package Twist\Core\Controllers
 */
class BaseREST extends Base{

    public function _baseCalls(){
        $this->_timeout(60);
    }

    public function _auth(){
        //Basic Auth is an API key, BaseRESTUser has a more advance auth
    }

    public function _cache(){
        //Cache a response
    }

    public function _restRespond(){
        //Output XML or JSON
    }

}