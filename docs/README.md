#TwistPHP Coding Conventions

##PHP
All PHP files should use the extension *.php*. Extensions such as *.php4*, *.php5* and *.phps* should be avoided.
PHP files should start with an opening PHP tag with no whitespace before and a blank line after. The closing php tag should always be omitted.
```php
<?php

    //Start code here
```
If creating a PHP file with a licence embedded then include the licence on line 2, straight after the opening PHP tag:
```php
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
	 * @link       http://twistphp.com/
	 *
	 */
```

###Indentation and Whitespace
PHP, JavaScript and CSS files should all use tabs for indentation. A tab consists of 4 spaces. If smart tabs is an option in your IDE, it should be used. All PHP should be indented once more than the opening `<?php` tag.
No more than two consecutive spaces or line breaks should be used.

####Comments
Single line comments simply need the opening `//` with no space before the first character of the comment.
Multi line comments should begin with a `/**`, use an extra space on each line before a ` * ` and then close with a ` */`. Comments for classes and methods should all be multi line comments, even if they only need a single line.
```php
	//Single line comment
	
	/**
	 * Multi line comment
	 */
```

####Namespaces, Classes and Methods
After starting your PHP file with an opening tag (and optionally the licence) followed by a blank line, you can declare your namespace parameters followed by a space.
An empty line should also be added:
* Before each method or method comment in a class
* After each method declaration in a class
```php
<?php

	namespace TwistPHP\Packages;
	use TwistPHP\ModuleBase;

	/*
	 * This is an example class
	 * It does nothing useful
	 */
	class Example {
		
		/*
		 * Let the user know that the class has been initiated
		 */
		public function __construct() {
			
			echo "I have begun";
		}
		
		/*
		 * Let the user know that the class has been initiated
		 */
		public function helloWorld() {
			
			return "Hello World!";
		}
	}
```

####Variables

###Method Comments
[FORMATTING]

Several method parameters have been supported by our documentation module in order to parse the PHP and create documentation.
| Parameter      | Expecting         | Quantity | Description                                                                    | Example                                                          |
| -------------- | ----------------- | -------- | ------------------------------------------------------------------------------ | ---------------------------------------------------------------- |
| @alias         | Method            | Single   | The method is an alias to another method                                       | `@alias anotherMethod`                                           |
| @related       | Method            | Single   | The method is related to another method                                        | `@related reverseMethod`                                         |
| @reference     | URL               | Single   | More information can be found at the URL given                                 | `@reference http://php.net/manual/en/function.date.php`          |
| @params        | Name, description | Multiple | Parameters used in the method - variable name followed by variable description | `@params $strEmail The users email address`                      |
| @throws        | Exception         | Single   | An exception that could be thrown by the method                                | `@throws \MyException`                                           |
| @return        | Type, description | Single   | The type and description of the expected return value                          | `@return Boolean True on success, false on failure`              |
| @note          | Comment           | Multiple | Free text field for keeping notes about the method                             | `@note This function is for backwards compatibility`             |
| @documentation | URL               | Single   | Link to the official documentation on twistphp.com/docs                        | `@documentation http://twistphp.com/docs/core/Tools/array3dTo2d` |
| @extends       | Framework package | Single   | ???                                                                            | `@extends User`                                                  |

##Variable Names