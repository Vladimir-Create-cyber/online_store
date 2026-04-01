$(document).ready(function() {
	var input = document.querySelector("#input-payment-telephone");
    if($(input).length){
    	window.intlTelInput(input, {
            initialCountry: "auto",
            geoIpLookup: function(success, failure) {
                $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                var countryCode = (resp && resp.country) ? resp.country : "us";
                success(countryCode);
                });
            },
    		autoPlaceholder:"aggressive",
      		utilsScript: "catalog/view/javascript/theme_lightshop/utils.js",
    	});
    }
	var input = document.querySelector("#form-amocrm input[name=tel]");
    if($(input).length){
    	window.intlTelInput(input, {
            initialCountry: "auto",
            geoIpLookup: function(success, failure) {
                $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                var countryCode = (resp && resp.country) ? resp.country : "us";
                success(countryCode);
                });
            },
    		autoPlaceholder:"aggressive",
      		utilsScript: "catalog/view/javascript/theme_lightshop/utils.js",
    	});
    }
    

    var input = document.querySelector("#modal_form-amocrm input[name=tel]");
    if($(input).length){
    	window.intlTelInput(input, {
            initialCountry: "auto",
            geoIpLookup: function(success, failure) {
                $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                var countryCode = (resp && resp.country) ? resp.country : "us";
                success(countryCode);
                });
            },
    		autoPlaceholder:"aggressive",
      		utilsScript: "catalog/view/javascript/theme_lightshop/utils.js",
    	});
    }
    
    
    
});