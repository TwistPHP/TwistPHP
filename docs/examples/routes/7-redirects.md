# Redirects

Redirect to another URI or Site

```php
//Temporary redirect 302
Twist::Route() -> redirect('/twistphp','https://twitter.com/twsitphp');

//Make the redirect permanent 301
Twist::Route() -> redirect('/twistphp','https://twitter.com/twsitphp',true);
```