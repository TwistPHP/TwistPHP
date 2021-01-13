# Getting started with Hooks

A hook is a place that allows you to insert additional customized programming. For example, you might want to run some specific code every time a new user is created. You would achive this by registering a hook in the framework.

## Hook Types

Each hook accepts an input type, there are a couple of different types of hooks that are used within the framework, these can be seen below:

| Type             | Example | Description                                                                                        |
| ---------------- | ------- | -------------------------------------------------------------------------------------------------- |
| Text             | Boolean | Status of the SQL query (`true` if successfully run, `false` otherwise)                            |
| JSON             | String  | The compiled SQL statement that was run                                                            |
| Model            | Integer | The ID of the newly inserted row (on `INSERT` queries only)                                        |

## Register and Canceling a hooks

Hooks can be registered and canceled at any point using a line of PHP. The hook can either be perminent in which case it will be sotred in the database and used every time the hook is called or it can be runtime which will only last for the current scripts execution.

To register a hook you will need to enter four things, hook key, a unique identifier, the hook itself and decide if to make the hook permanent.
```php
<?php

	\Twist::framework()->hooks()->register('TWIST_EMAIL_PROTOCOLS','phpmailer',array('model' => 'Packages\phpmailer\Models\Send'),true);

```

To remove a hook from the system you will need the hook key and its unique identifier, simply call the cancel function of the hooks model and pass in the two parameters. An example can be seen below:

```php
<?php

	\Twist::framework()->hooks()->cancel('TWIST_EMAIL_PROTOCOLS','phpmailer-email-protocol',true);

```