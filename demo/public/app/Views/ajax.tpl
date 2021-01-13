<!DOCTYPE html>
<html class="no-js" lang="en-GB">
<head>
    <meta charset="utf-8">
    <title>TwistPHP - The PHP MVC Framework with a TWIST</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
</head>
<body>
<h1>AJAX</h1>
<form id="test-form">
    <input name="firstname" type="text" value="Philip"> <input name="lastname" type="text" value="Fry"> <input name="dob" type="date" value="1974-08-14">
</form>
<script src="https://cdnjs.cloudflare.com/ajax/libs/babel-polyfill/6.26.0/polyfill.min.js" integrity="sha256-WRc/eG3R84AverJv0zmqxAmdwQxstUpqkiE+avJ3WSo=" crossorigin="anonymous"></script>
{resource:ajax}
<script>
	var door = new twistajax( '/ajax' );

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

	door.postForm( 'age2', '#test-form' )
			.then( response => {
				console.log( response );
			} )
			.catch( e => {
				console.error( 'Something broke:', e );
			} );
</script>
</body>
</html>