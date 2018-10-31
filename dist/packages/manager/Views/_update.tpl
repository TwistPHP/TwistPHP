<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-gb" dir="ltr">
    <head>
        <title>Update | TwistPHP Manager</title>
        <!--================================ META ================================-->
        <meta charset="utf-8">
        <!--[if lt IE 9]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->

        <!--================================ THIRD-PARTY RESOURCES ================================-->
        <!-- TEMPORARY FIX -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/unsemantic/1.2.3/unsemantic-grid-responsive-tablet-no-ie7.min.css" type="text/css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" type="text/css" rel="stylesheet">
        <link href="https://rawgit.com/ahosgood/Arable/master/dist/arable.min.css" type="text/css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

        <!--================================ CSS ================================-->
        <link href="{resource:core-uri}manager/css/base.css" type="text/css" rel="stylesheet">

        <!--================================ JAVASCRIPT ================================-->
        <!--<script src="{resource:core-uri}manager/js/base.js"></script>-->

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
                <h1 class="no-top-margin"><img src="{route:interface_uri}/resources/images/logo.png">TwistPHP Updater</h1>
            </div>
            <div class="grid-80 prefix-10 tablet-grid-90 tablet-prefix-5 mobile-grid-100">
                <div id="progress-percentage-wrapper">
                    <div id="progress-percentage"></div>
                </div>
                <div id="progress"></div>
                <div id="complete-actions"><a href="" class="button">Retry</a><a href="#" id="back-to-manager" class="button blue">Back to Manager</a></div>
            </div>
            <iframe id="init-update" src="{route:interface_uri}/scripts/update.php"></iframe>
            <script>
                jQuery(
                    function( $ ) {
                        /* ===================================================== IFRAME ===================================================== */
                        var jqoProgress = $( '#progress' ),
                                jqoProgressPercentage = $( '#progress-percentage' ),
                                getProgress = function() {
                                    $.get( 'progress',
                                        function( objResponse ) {
                                            jqoProgress.html( objResponse.html ).scrollTop( 99999 );
                                            jqoProgressPercentage.width( objResponse.progress + '%' );
                                            if( objResponse.complete ) {
                                                $( '#progress-percentage' ).css( 'opacity', 0 );
                                                if( objResponse.success ) {
                                                    jqoProgress.html( jqoProgress.html() + '<br>&gt; Redirecting...' );
                                                    setTimeout(
                                                        function() {
                                                            window.location = objResponse.manager_url;
                                                        }, 3000
                                                    );
                                                } else {
                                                    $( '#back-to-manager' ).attr( 'href', objResponse.manager_url );
                                                    $( '#complete-actions' ).show();
                                                }
                                            } else {
                                                setTimeout( getProgress, 500 );
                                            }
                                        }
                                    );
                                };

                        getProgress();
                    }
                );
            </script>
        </div>
    </body>
</html>