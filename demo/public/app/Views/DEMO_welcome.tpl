<!DOCTYPE html>
<html class="no-js" lang="en-GB">
<head>
    <meta charset="utf-8">
    <title>TwistPHP - The PHP MVC Framework with a TWIST</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    {resource:arable}
    {resource:modernizr}
</head>
<body>
<h1>Welcome!</h1>
<h2>What a beautiful {date:l} it is :)</h2>
<form id="test-form">
    <input name="firstname" type="text" value="Philip"> <input name="lastname" type="text" value="Fry"> <input name="dob" type="date" value="1974-08-14">
</form>
{resource:babel,polyfill}
{resource:twist/ajax}
<script>
	var door = new twistajax( '/ajax' );

//	door.debug = true;

	door.get( 'knock' )
			.then( response => {
				console.log( response );
			} )
			.catch( e => {
				console.error( 'Sorry, I didn\'t hear you knock because: ' + e );

				door.get( 'ring' )
						.then( response => {
							console.log( response );
						} )
						.catch( e => {
							console.error( 'Sorry, I didn\'t hear you ring because: ' + e );
						} );
			} );

	door.post( 'age', {
		firstname: 'Andrew',
		lastname: 'Hosgood',
		dob: '1986-09-24'
	} )
			.then( response => {
				console.log( response );
			} )
			.catch( e => {
				console.error( 'Something broke:', e );
			} );

	door.postForm( 'age', '#test-form' )
			.then( response => {
				console.log( response );
			} )
			.catch( e => {
				console.error( 'Something broke:', e );
			} );
</script>
</body>
</html>