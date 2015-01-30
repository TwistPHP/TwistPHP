#Further Templating

##More Template Tags

There are more template tags included to help you separate your PHP and HTML.

| Tag                               | Type                   | Description                                                                                                                                 |
| --------------------------------- | ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------- |
| `{template:my/template/file.tpl}` | Template Include       | Include another template directly into this location, templates are processed using the template module and current data set                |
| `{element:my/element/file.php}`   | Element Include        | Include the results of a PHP element directly onto the page                                                                                 |
| `{data:name}`                     | Data                   | When building a template using the module you can pass in an array of tags, this option will output the selected tag to the template        |
| `{data:products/television/name}` | Multi-dimensional Data | Same as '{data:option}' apart from multi-dimensional array of tags can be passed in. This function required 'Snipit' module to be installed |
| `{date:Y-m-d H:i:s}`              | Date                   | Output a formatted version of today's date. See the [PHP date() function on php.net](http://php.net/manual/en/function.date.php).           |
| `{get:option}`                    | Get Parameters         | Output the contents of any PHP $_GET parameter directly to the template                                                                     |
| `{post:option}`                   | Post Parameters        | Output the contents of any PHP $_POST parameter directly to the template                                                                    |
| `{server:option}`                 | Server Parameters      | Output the contents of any PHP $_SERVER parameter directly to the template                                                                  |
| `{cookie:option}`                 | Cookie Parameters      | Output the contents of any PHP $_COOKIE parameter directly to the template                                                                  |

##Conditional Template Tags

Conditional template tags allow you to use logic and arguments to output dynamic data similar to an inline PHP if() statement.

The use the format `{ARGUMENT?TRUE:FALSE}`.

Below are all the comparison types that you can use between your condition and values

| Logic | Argument           | TRUE when                                                               |
| ----- | ------------------ | ----------------------------------------------------------------------- |
| ==    | `variable==value`  | The variable is equal to the value                                      |
| ===   | `variable===value` | The variable is equal to the value and they are of the same data type   |
| !=    | `variable!=value`  | The variable is not equal to the value                                  |
| !==   | `variable!==value` | The variable is not equal to the value or they are different data types |
| <     | `variable<value`   | The variable is less than value                                         |
| <=    | `variable<=value`  | The variable is less than or equal to the value                         |
| >     | `variable>value`   | The variable is greater than value                                      |
| >=    | `variable>=value`  | The variable is greater than or equal to the value                      |
| *     | `variable*value`   | The variable is an array and the value is contained within it           |
| *=    | `variable*=value`  | The value is a string contained within the variable                     |
| ^=    | `variable^=value`  | The variable begins with the value                                      |
| $=    | `variable$=value`  | The variable ends with the value                                        |

###Examples

```
{date:L==1?"It's a leap year!":"It's just another year"}
```

```
{date:g<12?template:morning.tpl:element:afternoon.php}
```

##Function Modifiers

Using some predefined functions, you can modify the data before it is output to the screen without having to resort to PHP.

| Tag                             | PHP Function                                                                   |
| ------------------------------- | ------------------------------------------------------------------------------ |
| `{addslashes[type:data]}`       | [addslashes](http://uk1.php.net/manual/en/function.addslashes.php)             |
| `{base64_decode[type:data]}`    | [base64_decode](http://uk1.php.net/manual/en/function.base64-decode.php)       |
| `{base64_encode[type:data]}`    | [base64_encode](http://uk1.php.net/manual/en/function.base64-encode.php)       |
| `{ceil[type:data]}`             | [ceil](http://uk1.php.net/manual/en/function.ceil.php)                         |
| `{escape[type:data]}`           | Alias for htmlspecialchars                                                     |
| `{floor[type:data]}`            | [floor](http://uk1.php.net/manual/en/function.floor.php)                       |
| `{htmlentities[type:data]}`     | [htmlentities](http://uk1.php.net/manual/en/function.htmlentities.php)         |
| `{htmlspecialchars[type:data]}` | [htmlspecialchars](http://uk1.php.net/manual/en/function.htmlspecialchars.php) |
| `{json_decode[type:data]}`      | [json_decode](http://uk1.php.net/manual/en/function.json-decode.php)           |
| `{json_encode[type:data]}`      | [json_encode](http://uk1.php.net/manual/en/function.json-encode.php)           |
| `{md5[type:data]}`              | [md5](http://uk1.php.net/manual/en/function.md5.php)                           |
| `{round[type:data]}`            | [round](http://uk1.php.net/manual/en/function.round.php)                       |
| `{sha1[type:data]}`             | [sha1](http://uk1.php.net/manual/en/function.sha1.php)                         |
| `{stripslashes[type:data]}`     | [stripslashes](http://uk1.php.net/manual/en/function.stripslashes.php)         |
| `{strip_tags[type:data]}`       | [strip_tags](http://uk1.php.net/manual/en/function.strip-tags.php)             |
| `{strtolower[type:data]}`       | [strtolower](http://uk1.php.net/manual/en/function.strtolower.php)             |
| `{strtoupper[type:data]}`       | [strtoupper](http://uk1.php.net/manual/en/function.strtoupper.php)             |
| `{ucfirst[type:data]}`          | [ucfirst](http://uk1.php.net/manual/en/function.ucfirst.php)                   |
| `{ucwords[type:data]}`          | [ucwords](http://uk1.php.net/manual/en/function.ucwords.php)                   |
| `{urldecode[type:data]}`        | [urldecode](http://uk1.php.net/manual/en/function.urldecode.php)               |
| `{urlencode[type:data]}`        | [urlencode](http://uk1.php.net/manual/en/function.urlencode.php)               |