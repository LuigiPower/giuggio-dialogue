(function(){
    "use strict";
    var app = angular.module('speech-module');

    app.directive("giuggioSpeech", ['ajax-service', function (ajax){
    	return{
    		restrict: 'E',
    		templateUrl: "template/speech.html",
    		controller: "giuggioSpeechController"
    	};
    }]);
})();
