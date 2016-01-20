# Modifying a Package

If you need to make modifications to a package you should not modify the core package code, you can safely modify the package in your `apps/Packages` folder.
Following these steps will allow you to update the package in the future but still keeping any overrides that you have made.

In this example we are working form the "ExampleContactForm" package.

## Modify a view

To add extra information to the thank you page follow the below steps:

1. In your Apps folder create the path `apps/Packages/ExampleContactForm/Views`
2. Copy the file `packages/ExampleContactForm/Views/thankyou.tpl` into your `apps/Packages/ExampleContactForm/Views` folder
3. Now edit the new `thankyou.tpl` file adding in the below code as the last line

```html
    
    <p>Please continue to browse our <a href="http://{setting:SITE_HOST}/">website</a>.</p>
    
```

Now when navigating to `/contact/thankyou` on your website you will see the extra text we have just added


## Modify a model

Now we are going to add an 'city' field onto the contact form, to do this we will need to edit the `Contact.model.php` model and also add the new field to the `form.tpl` view.

First we are going to add extra field to the model:

1. In your Apps folder create the path `apps/Packages/ExampleContactForm/Models`
2. Copy the file `packages/ExampleContactForm/Models/Contact.model.php` into your `apps/Packages/ExampleContactForm/Models` folder
3. Now edit the new `Contact.model.php` file, we are going to replace the `validate()` function with the below code

```php
<?php

    public static function validate(){
    
        $blStatus = false;

        return $blStatus;
    }
    
```

Next add the new field to the `form.tpl` view:

1. In your Apps folder create the path `apps/Packages/ExampleContactForm/Views` (if you have not already)
2. Copy the file `packages/ExampleContactForm/Views/form.tpl` into your `apps/Packages/ExampleContactForm/Views` folder
3. Now edit the new `form.tpl` file, we are going to add the below line of code after the name field:

```html

   <label>City</label><input type="text" name="city" value="">
    
```

Now when navigating to `/contact` on your website you will see the new field on the form, when the form is filled out you will also see the extra field listed in the alert email.