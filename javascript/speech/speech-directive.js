(function(){
    "use strict";
    var app = angular.module('speech-module');

    app.directive("m0ResourceEdit", ['ajax-service', function (ajax){
    	return{
    		restrict: 'E',
    		templateUrl: "template/speech.html",
    		controller: "giuggioSpeech"
    	};
    }]);
})();
