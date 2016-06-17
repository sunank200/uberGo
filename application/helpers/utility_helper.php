<?php

function asset_url() {
    return base_url().'assets/';
}

function get_tz_logo($purpose = 'home') {
    switch ($purpose) {
        case 'home':
            return base_url()."/assets/images/logo.png";
        case 'side-menu':
           return base_url()."/assets/images/logo.png";
        default:
          return base_url()."/assets/images/logo.png";

    }
}

?>