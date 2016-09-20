<?php

function redirect_to($location = NULL ) {
    if($location != NULL) {
        header("location: {$location}");
        exit;
    }
}

function mysql_prep( $input ) {
    if (is_array($input)) {
        foreach($input as $var=>$val) {
            $output[$var] = mysql_prep($val);
        }
    } else {
        if (get_magic_quotes_gpc()) {
            $input = stripslashes($input);
        }
        $input  = cleanInput($input);
        $output = mysql_real_escape_string($input);
    }
    return $output;
}
function cleanInput($input) {
    $search = array(
        '@<script[^>]*?>.*?</script>@si',   // Strip out javascript
        '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
        '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
    );
    //'@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments

    $output = preg_replace($search, '', $input);
    return $output;
}