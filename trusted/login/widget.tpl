<html>
    <head>
        <title>Trusted Login</title>
    </head>
    <body>
        <script>
            var trustedLogin = "trusted-login";

            function parseQueryParapams() {
                var query_string = {};
                var query = window.location.search.substring(1);
                var vars = query.split("&");
                for (var i = 0; i < vars.length; i++) {
                    var pair = vars[i].split("=");
                    // If first entry with this name
                    if (typeof query_string[pair[0]] === "undefined") {
                        query_string[pair[0]] = decodeURIComponent(pair[1]);
                        // If second entry with this name
                    } else if (typeof query_string[pair[0]] === "string") {
                        var arr = [query_string[pair[0]], decodeURIComponent(pair[1])];
                        query_string[pair[0]] = arr;
                        // If third or later entry with this name
                    } else {
                        query_string[pair[0]].push(decodeURIComponent(pair[1]));
                    }
                }
                return query_string;
            }

            function $_GET(name, def) {
                var params = parseQueryParapams();
                if (name in params)
                    return params[name];
                else if (typeof def !== "undefined")
                    return def;
                return null;
            }

            try {
                var code = $_GET("code");
                console.log(window.opener);
                if (window.opener) {
                    // Edge не имеет свойство opener
                    window.opener.location.href = "<?php echo TRUSTED_AUTH_REDIRECT_URI?>?final=true&code=" + code;
                }
                window.close();
            }
            catch (e) {
                window.document.writeln("<h3>Trusted ligin frame: " + e.message + "</h3>");
                window.document.writeln("<pre>");
                window.document.writeln(e.stack);
                window.document.writeln("</pre>");
            }
        </script>
    </body>
</html>