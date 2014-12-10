#Variables

##Format

Variable names should be in lower camel case, for example `lowerCamelCase` where the first letter is lower case. Where one of the prefixes below is used, the prefix should be in lower case and the name start with a capital letter.

The variable name should make sense and describe its contents: `$t` is not acceptable, however `$arrAbsoluteSearchTerms` is.

##Prefixes

Variables should be prefixed as follows to increase ease of use:

| Type                     | Prefix     | Example Name    | Example Data                                |
| ------------------------ | ---------- | --------------- | ------------------------------------------- |
| String                   | str        | `$strName`      | `"Andrew"`                                  |
| Integer                  | int        | `$intChannels`  | `29`                                        |
| Float                    | flt        | `$fltPi`        | `3.141592654`                               |
| Array                    | arr        | `$arrDays`      | `Array("Mo","Tu","We","Th","Fr","Sa","Su")` |
| Boolean                  | bl         | `$blExists`     | `true`                                      |
| Directory                | dir        | `$dirResources` | `"/var/www/public_html"`                    |
| JSON                     | json/jso   | `$jsonUser`     | `{"name":"Andrew","level":"1337"}`          |
| Resource                 | res        | `$resDatabase`  | `(resource) Database`                       |
| Object                   | obj        | `$resUser`      | `(object) User`                             |
| Timestamp                | tim        | `$timStart`     | `"1386331200"`                              |
| Date                     | dat        | `$datStart`     | `"2013-12-06T12:00:00+00:00"`               |
| Function                 | fun        | `$funValidate`  | `function() {}`                             |
| Mixed (can be any type)  | mxd        | `$mxdReturn`    |                                             |