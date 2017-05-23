(function(){
    "use strict";
    var app = angular.module('speech-module');

    app.controller("m0ResourceEditController", ['$scope', 'ajax-service', 'widget-service', 'session-service', 'speech-service', function ($scope, ajax, widget, session, speech){

        $scope.target_text = "";

        $scope.asr_listener = {
            onstart: function() {

            },
            onend: function() {

            },
            onresult: function(ev) {
                console.log("Got speech results:");
                console.log(ev);
                $scope.asr_results  = ev.results;//[0][0].transcript;
                $scope.target_text = $scope.asr_results[0][0].transcript;
            }
        };

        $scope.startASR = function() {
            speech.startASR();
        };

        $scope.startTTS = function() {
            speech.startTTS($scope.target_text);
        };

    }]);
})();
