#Routing Rules

```php
Twist::Route() -> controller( '/%', '' );
Twist::Route() -> controller( '/shop/%', '' );
Twist::Route() -> controller( '/gallery/{category}/%', '' );
Twist::Route() -> manager();
Twist::Route() -> view();
Twist::Route() -> restrict( '/admin/%' );
Twist::Route() -> restrictMembers( '/admin/%' );
Twist::Route() -> restrictAdvanced( '/admin/%' );
Twist::Route() -> restrictAdmin( '/admin/%' );
Twist::Route() -> restrictSuperAdmin( '/admin/%' );
```