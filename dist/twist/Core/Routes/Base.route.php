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

	namespace Twist\Core\Routes;
	use Twist\Core\Utilities\Route;

	/**
	 * The base route that all route files must extend in order for Twist to be able to process them correctly. This base class added some core features required to initialise a route file.
	 * @package Twist\Core\Routes
	 */
	class Base extends Route{

		protected $strInterfaceKey = null;
		protected $strBaseURI = null;
		protected $strBaseTemplate = null;
		protected $resRoute = null;

		/**
		 * Called when the routes package launches a routes file. A routes file is usually a pre-defined set of route found withing an installable package.
		 * @param $strPackageKey
		 */
		public function __construct($strPackageKey){

			parent::__construct($strPackageKey);

			//Get the current base template before it is purged
			$this->baseView(\Twist::Route()->baseView());
			\Twist::Route()->purge();

			$arrPackageParams = \Twist::framework()->package()->information(strtolower($strPackageKey));

			$this->packageURI($strPackageKey);

			if(array_key_exists('path',$arrPackageParams)){
				$this->setDirectory(sprintf('%s/views/', rtrim($arrPackageParams['path'], '/')));
				$this->setControllerDirectory(sprintf('%s/controllers/', rtrim($arrPackageParams['path'], '/')));
			}
		}

		/**
		 * Function to detect if a package exists or has been installed
		 * @param $strModule
		 * @throws \Exception
		 */
		protected function packageRequired($strModule){
			\Twist::framework()->package()->exists($strModule, true);
		}

		/**
		 * Fall back function to determine if the extending class has got a load function. This function must be over-ridden for the route file to work correctly.
		 * @throws \Exception
		 */
		public function load(){
			throw new \Exception('A load function must be added to your interface class, the class must extend TwistInterface');
		}
	}