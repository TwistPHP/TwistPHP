#Indentation and Whitespace

PHP, JavaScript and CSS files should all use tabs for indentation. A tab consists of 4 spaces. If smart tabs is an option in your IDE, it should be used. All PHP should be indented once more than the opening `<?php` tag.

No more than two consecutive spaces or line breaks should be used.

##Comments

Single line comments simply need the opening `// ` with a space before the first character of the comment.

Multi-line comments should begin with a `/**`, use an extra space on each line before a ` * ` and then close with a ` */`. Comments for classes and methods should all be multi line comments, even if they only need a single line.

```php
	//Single line comment

	/**
	 * Multi line comment
	 */
```