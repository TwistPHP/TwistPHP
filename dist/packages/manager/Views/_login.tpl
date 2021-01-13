<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-gb" dir="ltr">
    <head>
        <title>Login | TwistPHP Manager</title>
        <!--================================ META ================================-->
        <meta charset="utf-8">
        <!--[if lt IE 9]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->

        <!--================================ THIRD-PARTY RESOURCES ================================-->
        <!-- TEMPORARY FIX -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/unsemantic/1.2.3/unsemantic-grid-responsive-tablet-no-ie7.min.css" type="text/css" rel="stylesheet">
        <link href="https://rawgit.com/ahosgood/Arable/master/dist/arable.min.css" type="text/css" rel="stylesheet">

        <!--================================ INTERFACE RESOURCES ================================-->
        {meta:tags}

        <!--================================ LINKED DOCUMENTS ================================-->
        <link rel="shortcut icon" type="image/x-icon" href="{core:logo-favicon}">

        <!--================================ MOBILE STUFF ================================-->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    </head>
    <body>
        <div class="grid-container">
            <div class="grid-100 tablet-grid-100 mobile-grid-100">
                <h1 class="no-top-margin"><img src="{resource:core-uri}logos/logo.png">TwistPHP</h1>
            </div>
            <div class="grid-80 prefix-10 tablet-grid-90 tablet-prefix-5 mobile-grid-90 mobile-prefix-5 grid-parent">
                {route:response}
            </div>
        </div>
    </body>
</html>