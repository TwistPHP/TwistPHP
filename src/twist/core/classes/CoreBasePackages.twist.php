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

	if(!class_exists('CoreBasePackages')){
		class CoreBasePackages extends CoreBase{

			//Used when calling modules that are not 3rd party
			public static function __callStatic($strModuleName, $arrArguments){

				$strObjectRef = sprintf('mod%s',$strModuleName);
				$strModule = sprintf('\Twist\Packages\%s',$strModuleName);

				\Twist::framework() -> package() -> load($strModuleName);
				$resTwistModule = (!Instance::isObject($strObjectRef)) ? new $strModule() : Instance::retrieveObject($strObjectRef);
				Instance::storeObject($strObjectRef,$resTwistModule);
				return $resTwistModule;
			}

			/**
			 * @deprecated
			 */
			public static function Template(){ return parent::View( count(func_get_args()) ? func_get_arg(0) : 'twist' ); }

			public static function framework(){ return parent::framework(); }
			public static function AJAX(){ return parent::AJAX(); }
			public static function Archive(){ return parent::Archive(); }
			public static function Asset(){ return parent::Asset(); }
			public static function CSV(){ return parent::CSV(); }
			public static function Cache(){ return parent::Cache( count(func_get_args()) ? func_get_arg(0) : 'twist' ); }
			public static function Command(){ return parent::Command(); }
			public static function Curl(){ return parent::Curl(); }
			public static function Database(){ return parent::Database( count(func_get_args()) ? func_get_arg(0) : 'twist' ); }
			public static function DateTime(){ return parent::DateTime(); }
			public static function Email(){ return parent::Email(); }
			public static function File(){ return parent::File(); }
			public static function FTP(){ return parent::FTP(); }
			public static function ICS(){ return parent::ICS(); }
			public static function Image(){ return parent::Image(); }
			public static function Localisation(){ return parent::Localisation(); }
			public static function Route(){ return parent::Route( count(func_get_args()) ? func_get_arg(0) : 'twist' ); }
			public static function Session(){ return parent::Session(); }
			public static function Timer(){ return parent::Timer( count(func_get_args()) ? func_get_arg(0) : 'twist' ); }
			public static function User(){ return parent::User(); }
			public static function Validate(){ return parent::Validate(); }
			public static function View(){ return parent::View( count(func_get_args()) ? func_get_arg(0) : 'twist' ); }
			public static function XML(){ return parent::XML(); }

			public static function Amazon(){
				\Twist::framework() -> package() -> load('Amazon');
				$resTwistModule = (!Instance::isObject('modAmazon')) ? new \Twist\Packages\Amazon() : Instance::retrieveObject('modAmazon');
				Instance::storeObject('modAmazon',$resTwistModule);
				return $resTwistModule;
			}

			public static function Blog(){
				\Twist::framework() -> package() -> load('Blog');
				$resTwistModule = (!Instance::isObject('modBlog')) ? new \Twist\Packages\Blog() : Instance::retrieveObject('modBlog');
				Instance::storeObject('modBlog',$resTwistModule);
				return $resTwistModule;
			}

			public static function Content(){
				\Twist::framework() -> package() -> load('Content');
				$resTwistModule = (!Instance::isObject('modContent')) ? new \Twist\Packages\Content() : Instance::retrieveObject('modContent');
				Instance::storeObject('modContent',$resTwistModule);
				return $resTwistModule;
			}

			public static function DomainTools(){
				\Twist::framework() -> package() -> load('DomainTools');
				$resTwistModule = (!Instance::isObject('modDomainTools')) ? new \Twist\Packages\DomainTools() : Instance::retrieveObject('modDomainTools');
				Instance::storeObject('modDomainTools',$resTwistModule);
				return $resTwistModule;
			}

			public static function Form(){
				\Twist::framework() -> package() -> load('Form');
				$resTwistModule = (!Instance::isObject('modForm')) ? new \Twist\Packages\Form() : Instance::retrieveObject('modForm');
				Instance::storeObject('modForm',$resTwistModule);
				return $resTwistModule;
			}

			public static function Gallery(){
				\Twist::framework() -> package() -> load('Gallery');
				$resTwistModule = (!Instance::isObject('modGallery')) ? new \Twist\Packages\Gallery() : Instance::retrieveObject('modGallery');
				Instance::storeObject('modGallery',$resTwistModule);
				return $resTwistModule;
			}

			public static function Link(){
				\Twist::framework() -> package() -> load('AJAX');
				$resTwistModule = (!Instance::isObject('modLink')) ? new \Twist\Packages\Link() : Instance::retrieveObject('modLink');
				Instance::storeObject('modLink',$resTwistModule);
				return $resTwistModule;
			}

			public static function Payment(){
				\Twist::framework() -> package() -> load('Payment');
				$resTwistModule = (!Instance::isObject('modPayment')) ? new \Twist\Packages\Payment() : Instance::retrieveObject('modPayment');
				Instance::storeObject('modPayment',$resTwistModule);
				return $resTwistModule;
			}

			public static function PhantomJS(){
				\Twist::framework() -> package() -> load('PhantomJS');
				$resTwistModule = (!Instance::isObject('modPhantomJS')) ? new \Twist\Packages\PhantomJS() : Instance::retrieveObject('modPhantomJS');
				Instance::storeObject('modPhantomJS',$resTwistModule);
				return $resTwistModule;
			}

			public static function QR(){
				\Twist::framework() -> package() -> load('QR');
				$resTwistModule = (!Instance::isObject('modQR')) ? new \Twist\Packages\QR() : Instance::retrieveObject('modQR');
				Instance::storeObject('modQR',$resTwistModule);
				return $resTwistModule;
			}

			public static function Resources(){
				\Twist::framework() -> package() -> load('Resources');
				$resTwistModule = (!Instance::isObject('modResources')) ? new \Twist\Packages\Resources() : Instance::retrieveObject('modResources');
				Instance::storeObject('modResources',$resTwistModule);
				return $resTwistModule;
			}

			public static function RSS(){
				\Twist::framework() -> package() -> load('RSS');
				$resTwistModule = (!Instance::isObject('modRSS')) ? new \Twist\Packages\RSS() : Instance::retrieveObject('modRSS');
				Instance::storeObject('modRSS',$resTwistModule);
				return $resTwistModule;
			}

			public static function Sass(){
				\Twist::framework() -> package() -> load('Sass');
				$resTwistModule = (!Instance::isObject('modSass')) ? new \Twist\Packages\Sass() : Instance::retrieveObject('modSass');
				Instance::storeObject('modSass',$resTwistModule);
				return $resTwistModule;
			}

			public static function Shopping(){
				\Twist::framework() -> package() -> load('Shopping');
				$resTwistModule = (!Instance::isObject('modShopping')) ? new \Twist\Packages\Shopping() : Instance::retrieveObject('modShopping');
				Instance::storeObject('modShopping',$resTwistModule);
				return $resTwistModule;
			}

			public static function Sitemap(){
				\Twist::framework() -> package() -> load('Sitemap');
				$resTwistModule = (!Instance::isObject('modSitemap')) ? new \Twist\Packages\Sitemap() : Instance::retrieveObject('modSitemap');
				Instance::storeObject('modSitemap',$resTwistModule);
				return $resTwistModule;
			}

			public static function SMS(){
				\Twist::framework() -> package() -> load('SMS');
				$resTwistModule = (!Instance::isObject('modSMS')) ? new \Twist\Packages\SMS() : Instance::retrieveObject('modSMS');
				Instance::storeObject('modSMS',$resTwistModule);
				return $resTwistModule;
			}

			public static function Snipit(){
				\Twist::framework() -> package() -> load('Snipit');
				$resTwistModule = (!Instance::isObject('modSnipit')) ? new \Twist\Packages\Snipit() : Instance::retrieveObject('modSnipit');
				Instance::storeObject('modSnipit',$resTwistModule);
				return $resTwistModule;
			}

			public static function SocialConnect(){
				\Twist::framework() -> package() -> load('SocialConnect');
				$resTwistModule = (!Instance::isObject('modSocialConnect')) ? new \Twist\Packages\SocialConnect() : Instance::retrieveObject('modSocialConnect');
				Instance::storeObject('modSocialConnect',$resTwistModule);
				return $resTwistModule;
			}

			public static function String(){
				\Twist::framework() -> package() -> load('String');
				$resTwistModule = (!Instance::isObject('modString')) ? new \Twist\Packages\String() : Instance::retrieveObject('modString');
				Instance::storeObject('modString',$resTwistModule);
				return $resTwistModule;
			}

			public static function Translation(){
				\Twist::framework() -> package() -> load('Translation');
				$resTwistModule = (!Instance::isObject('modTranslation')) ? new \Twist\Packages\Translation() : Instance::retrieveObject('modTranslation');
				Instance::storeObject('modTranslation',$resTwistModule);
				return $resTwistModule;
			}

			public static function VideoEncoding(){
				\Twist::framework() -> package() -> load('VideoEncoding');
				$resTwistModule = (!Instance::isObject('modVideoEncoding')) ? new \Twist\Packages\VideoEncoding() : Instance::retrieveObject('modVideoEncoding');
				Instance::storeObject('modVideoEncoding',$resTwistModule);
				return $resTwistModule;
			}

			public static function WebSockets(){
				\Twist::framework() -> package() -> load('WebSockets');
				$resTwistModule = (!Instance::isObject('modWebSockets')) ? new \Twist\Packages\WebSockets() : Instance::retrieveObject('modWebSockets');
				Instance::storeObject('modWebSockets',$resTwistModule);
				return $resTwistModule;
			}

			public static function WKHTML(){
				\Twist::framework() -> package() -> load('WKHTML');
				$resTwistModule = (!Instance::isObject('modWKHTML')) ? new \Twist\Packages\WKHTML() : Instance::retrieveObject('modWKHTML');
				Instance::storeObject('modWKHTML',$resTwistModule);
				return $resTwistModule;
			}
		}
	}