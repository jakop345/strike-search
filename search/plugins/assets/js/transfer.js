$(document)
    .ready(function () {




        function getQuery() {
            var form = document.forms['filters'];
            var query = form.phrase.value;
            if (isEmpty(query)) {
                return "";
            }
           
            return query; 
        }

        function isEmpty(str) {
            return (!str || 0 === str.length);
        }

        function redirect() {
            var localquery = getQuery();
            if (!isEmpty(localquery)) {
                localStorage.query = localquery;
            }
            window.location.replace("http://getstrike.net/torrents/");
        }



        function setup() {

            $("#search")
                .bind("click", function () {
                    redirect();
                });

            $("#searchform")
                .submit(function (e) {

                    e.preventDefault();
                    redirect();
                });



        }
        setup();

    });
