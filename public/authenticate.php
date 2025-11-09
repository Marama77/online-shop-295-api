 <?php
     function require_authenticate() {
        if (!isset($_COOKIE["token"]) || !Token::validate($_COOKIE["token"], $secret )) {
            return false;
    }
    return true;
}
?>
    
    
    

    

    