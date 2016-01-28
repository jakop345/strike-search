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

		
		function donatePrompt() {
		
		 var dialog = new BootstrapDialog({
            message: 'This project is not for profit, there will never be advertisements <br>If you wish to donate to help with hosting/API maintaining cost you can do so <br>Below is a button that will take you to paypal, alternatively our bitcoin address is 1DpnZFPikokWwab4muVbjUXZDAD25DhXj3',
            buttons: [{
                id: 'btn-1',
                label: 'Donate via Paypal'
            }]
        });
        dialog.realize();
        var btn1 = dialog.getButton('btn-1');
        btn1.click({'name': 'Apple'}, function(event){
           var link = "https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=TWHNPSC7HRNR2";
 window.open(link);
        });
        dialog.open();
		
		}

        function redirect() {
            var localquery = getQuery();
            if (!isEmpty(localquery)) {
                localStorage.query = localquery;
            }
            window.location.replace("http://getstrike.net/torrents/");
        }



        function setup() {
		$("a.ajaxLink").on('click', function(e) {
e.preventDefault();
 e.stopPropagation();
 var link = $(this).text();
 window.open(link);
 
});
            $("#search")
                .bind("click", function () {
                    redirect();
                });

				 $(document)
                .on("click", "#donate", function (e) {
                  donatePrompt();
                });
            $("#searchform")
                .submit(function (e) {

                    e.preventDefault();
                    redirect();
                });



        }
        setup();

    });
