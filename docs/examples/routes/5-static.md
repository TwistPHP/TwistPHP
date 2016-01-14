# Serving static content

```php
Twist::Route() -> view();
Twist::Route() -> postView();
Twist::Route() -> get('/test',function(){ return 'My Response'; });
Twist::Route() -> post();
```

Serve a file

```php
Twist::Route() -> file('/my-file','/path/to/my/file.zip');
```


Make the contents of a folder that may or may not already be in your public root available through the browser

```php
Twist::Route() -> file('/my-folder/%','/path/to/my/resources/folder');
```

Redirect to another URI or Site

```php
//Temporary redirect 302
Twist::Route() -> redirect('/twistphp','https://twitter.com/twsitphp');

//Make the redirect permanent 301
Twist::Route() -> redirect('/twistphp','https://twitter.com/twsitphp',true);
```