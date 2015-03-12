<?php
	/**
	 * This file is part of TwistPHP.
	 *
	 * TwistPHP is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation, either version 3 of the License, or
	 * (at your option) any later version.
	 *
	 * TwistPHP is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with TwistPHP.  If not, see <http://www.gnu.org/licenses/>.
	 *
	 * @author     Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
	 * @license    https://www.gnu.org/licenses/gpl.html LGPL License
	 * @link       https://twistphp.com
	 *
	 */

	namespace Twist\Core\Classes;
	use Twist\Core\Packages\Route;

	class BaseInterface extends Route{

		protected $strInterfaceKey = null;
		protected $strBaseURI = null;
		protected $strBaseTemplate = null;
		protected $resRoute = null;

		public function __construct($strInterfaceKey){

			parent::__construct($strInterfaceKey);

			//Get the current base template before it is purged
			$this->baseView(\Twist::Route()->baseView());
			\Twist::Route()->purge();

			$arrInterfaceParams = \Twist::framework()->interfaces()->information($strInterfaceKey);

			$this->interfaceURI($strInterfaceKey);
			$this->setDirectory(sprintf('%s/views/',rtrim($arrInterfaceParams['path'],'/')));
			$this->setControllerDirectory(sprintf('%s/controllers/',rtrim($arrInterfaceParams['path'],'/')));
		}

		protected function moduleRequired($strModule){
			\Twist::framework()->module()->exists($strModule,true);
		}

		public function load(){
			throw new \Exception('A load function must be added to your interface class, the class must extend TwistInterface');
		}
	}