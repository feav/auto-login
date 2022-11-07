jQuery(document).ready(function() {
    var logify = localStorage.getItem("logify");


    const remoteLogin = function(url, email, pwd) {
        var win = window.open(url + "/wp-admin/admin-ajax.php?action=wp_remote_login&function=login&username=" + email + "&password=" + pwd, '_blank');
        if (win) {
            // setTimeout(() => { win.close(); }, 5000);
            localStorage.setItem("logify", 10);
        } else {
            alert('Autorisez les POPUP pour vous connecter automatiquement');
        }
    }
    const checkLogingTwoSide = function(url, email, pwd) {
        var $ajaxurl = url + "/wp-admin/admin-ajax.php";
        jQuery.getJSON($ajaxurl, {
                'function': 'getuser',
                'action': 'wp_remote_login',
            }, function(jsonData) {
                if (jsonData.response == 400) {
                    remoteLogin(url, email, pwd);
                }
            })
            .done(function(jsonData) {})
            .fail(function() {})
    }
    const checkLoging = function() {
        var $ajaxurl = "/wp-admin/admin-ajax.php";
        jQuery.getJSON($ajaxurl, {
                'function': 'getuser',
                'action': 'wp_auto_login',
            }, function(jsonData) {
                if (jsonData.response == 200) {
                    if (logify != 10)
                        remoteLogin(jsonData.data.site, jsonData.data.email, jsonData.data.pwd);
                } else {
                    localStorage.setItem("logify", 0);
                }
            })
            .done(function(jsonData) {})
            .fail(function() {})
    }

    checkLoging();
});