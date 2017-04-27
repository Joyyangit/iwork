<?php

function getgpc( $k, $v = NULL ) {
    if (isset($_GET[$k])) {
        return $_GET[$k];
    } elseif (isset($_POST[$k])) {
        return $_POST[$k];
    }
    return $v;
}

function aget( $a, $k, $default = NULL ) {
    if (array_key_exists($k, $a)) {
        return $a[$k];
    } else {
        return $default;
    }
}
