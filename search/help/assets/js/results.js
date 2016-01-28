$(document)
    .ready(function () {


        var isRequestFinished = true;


        function disableForm() {
            var form = document.forms['filters'];
            var elements = form.elements;
            for (var i = 0, len = elements.length; i < len; ++i) {
                elements[i].disabled = true;

            }
        }

        function enableForm() {
            var form = document.forms['filters'];
            var elements = form.elements;
            for (var i = 0, len = elements.length; i < len; ++i) {
                elements[i].disabled = false;

            }
        }

        function remoteSearch() {
            if (isRequestFinished) {
                isRequestFinished = false;
                var xmlHttpReq = false;
                var self = this;
                // Mozilla/Safari
                if (window.XMLHttpRequest) {
                    self.xmlHttpReq = new XMLHttpRequest();
                }
                // IE
                else if (window.ActiveXObject) {
                    self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
                }
                self.xmlHttpReq.open('POST', "core/callSearchEngine.php", true);
                self.xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                self.xmlHttpReq.onreadystatechange = function () {
                    if (self.xmlHttpReq.readyState == 4) {
                        $('#loadingicon')
                            .hide();
                        $('#sentback')
                            .hide()
                            .fadeOut('slow');
                        updatePage(self.xmlHttpReq.responseText);
                        isRequestFinished = true;

                        //  enableForm();

                    }
                };
                self.xmlHttpReq.send(getQuery());

                return false;
            }
        }

        function getQuery() {
            var form = document.forms['filters'];
            var query = form.phrase.value;
            if (isEmpty(query)) {
                return "";
            }
            var squery = 'query=' + encodeURIComponent(query);
            return squery; // NOTE: no '?' before querystring
        }

        function isEmpty(str) {
            return (!str || 0 === str.length);
        }

        function updatePage(str) {

            document.getElementById("data")
                .innerHTML = str;
            $('#data')
                .hide()
                .fadeIn('slow');
            $('#stats')
                .hide()
                .fadeIn('slow');
            $('#results')
                .hide()
                .fadeIn('slow');
            $.bootstrapSortable(true);
        }

        function search() {
            //  disableForm();
            remoteSearch();
        }

        function setup() {
            $('#loadingicon')
                .hide();
            $("#search")
                .bind("click", function () {
                   $( "#searchform" ).submit();
                });

            $("#searchform")
                .submit(function (e) {
                    $('#loadingicon')
                        .hide()
                        .fadeIn('slow');
                    $('#data')
                        .hide();
                    e.preventDefault();
                    search();
                });


            $(document)
                .on("click", "#bars", function (e) {
                    $('#table')
                        .toggleClass('table-condensed');
                });
            $(document)
                .on("click", "#toggle-lock", function (e) {
                    $("i", this)
                        .toggleClass('fa-lock fa-unlock');
                });
				
				$(document)
                .on("click", ".clickable-row", function (e) {
                       window.document.location = $(this).data("href");
                });
				
			
				
				 var localquery = localStorage.query;
            if (!isEmpty(localquery)) {
				$('#phrase').val(localquery);
				 $('#loadingicon')
                        .hide()
                        .fadeIn('slow');
                    $('#data')
                        .hide();
				search();
				localStorage.query = "";
            }
        }
        setup();

    });
