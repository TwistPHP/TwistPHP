#Methods

##Comments

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