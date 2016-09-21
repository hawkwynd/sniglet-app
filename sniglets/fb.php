<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scott
 * Date: 5/21/13
 * Time: 2:21 PM
 * To change this template use File | Settings | File Templates.
 */
?>
<div id="fb-root"></div>
<script>
    // Additional JS functions here
    window.fbAsyncInit = function() {
        FB.init({
            appId      : '130088633853494', // App ID
            channelUrl : '//www.musiccityguru.com/channel.html', // Channel File
            status     : true, // check login status
            cookie     : true, // enable cookies to allow the server to access the session
            xfbml      : true  // parse XFBML
        });

        // Additional init code here

    };

    // Load the SDK asynchronously
    (function(d){
        var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement('script'); js.id = id; js.async = true;
        js.src = "//connect.facebook.net/en_US/all.js";
        ref.parentNode.insertBefore(js, ref);
    }(document));
</script>