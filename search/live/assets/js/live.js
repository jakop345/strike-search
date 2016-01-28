$(document)
    .ready(function () {


        var hasFeedStarted = true;
		var lastID;
     
        function donateTo() {
          
         alertMessage("This page is only here as a demo of traffic diversity and will be gone soon, enjoy it!");
           
        }

        function alertMessage(str) {
            var dialog = new BootstrapDialog({
                message: str,
                buttons: [{
                    id: 'btn-1',
                    label: 'Donate!'
            }]
            });
            dialog.realize();
            var btn1 = dialog.getButton('btn-1');
            btn1.click({
                'name': 'Apple'
            }, function (event) {
                var link = "https://getstrike.net/torrents/donate/";
                window.open(link);
            });
            dialog.open();
        }
	

        function fetchLiveFeed() {
          $.post( "https://getstrike.net/torrents/core/feed.php", { key: "TSfN1H4kJ2Mi9tZy7B2VybhYHh4PtP4M" })
		.done(function( data ) {
		var phrase = data.phrase;
		var flag = data.flag;
		var date = data.date;
		var id = data.id;
		if (id === lastID) {
		console.log('Skip, some as last id');
			return false;
		} else {
			lastID = id;
			$("#searches").prepend($("<li><span class=\"phrase\">" +  phrase + "</span><br /><span class=\"flagicon flag-" + flag+ "\">" + "</span><br /><span class=\"date\">" + date + "</span></li>").fadeIn("slow"));
		}
		
  
		});
        }


		 donateTo();

		$( "#loadingmessage" ).fadeOut( "slow", function() {
		
		window.setInterval(function(){
 fetchLiveFeed();
}, 1200);
			 fetchLiveFeed();
		});
       
    });
