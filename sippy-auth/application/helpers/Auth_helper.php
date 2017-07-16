<?php



class Auth_helper {
    
    public static function logged_in() {
        if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"]) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}
