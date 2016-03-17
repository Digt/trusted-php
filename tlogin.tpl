<div id="trusted-login">Загрузка...</div>
<script>
    var config = {
        redirect_uri: "<?php echo TRUSTED_AUTH_REDIRECT_URI ?>",
        client_id: "<?php echo TRUSTED_LOGIN_CLIENT_ID ?>",
        login: {},
        social: {
            providers: ["trustednet", "vk", "fbook", "mailru", "google"],
            form: "circle",
            size: "big"
        }
    };

    var trusted_login = document.getElementById("trusted-login");
    trusted_login.setAttribute("data-config", JSON.stringify(config));
    window["trusted-login"].auth = function (code) {
        window.location.href = "<?php echo TRUSTED_AUTH_WIDGET_REDIRECT_URI ?>";
    };
</script>
<script src="<?=TRUSTED_LOGIN_PLUGIN_PATH?>js/tlogin-2.0.1.js"></script>