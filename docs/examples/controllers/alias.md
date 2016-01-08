# Method aliasing

If you would like to use hyphens in your URIs, simply adding a method to controller won't work, as PHP doesn't support functions with non-alphanumeric characters.

## Alias

To make an alias of a method, allowing it to be accessed by another URI, you can use the extended `_aliasURI()` method. Making an alias will allow both the method to be called by its original name as well as the registered alias.

```php
<?php

    namespace App\Controllers;
    
    use Twist\Core\Controllers\Base;
    
    class Aliases extends Base {
    
        public function __construct() {
            /*
             * --------------------------------
             * In the construct method when the
             * class is initialised, we can set
             * an alias for a request so either
             * the method name or the alias can
             * be used
             * --------------------------------
             */
            $this -> _aliasURI( 'search-your-feelings-you-know-it-to-be-true', 'searchyourfeelingsyouknowittobetrue' );
        }
    
        /*
         * --------------------------------
         * This URI would look a tad insane
         * and a bit confusing for our poor
         * user to decypher
         * --------------------------------
         */
        public function searchyourfeelingsyouknowittobetrue() {
            return 'Noooooooooooooooooooooooo!';
        }
        
    }
```