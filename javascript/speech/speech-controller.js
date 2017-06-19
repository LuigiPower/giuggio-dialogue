(function(){
    "use strict";
    var app = angular.module('speech-module');

    app.controller("giuggioSpeechController", ['$scope', 'ajax-service', 'widget-service', 'session-service', 'speech-service', function ($scope, ajax, widget, session, speech){

        $scope.target_text = "";
        $scope.preemptive = true;
        $scope.disable_followup = false;

        $scope.asr_listener = {
            onstart: function() {

            },
            onend: function() {

            },
            onresult: function(ev) {
                console.log("Got speech results:");
                console.log(ev);
                $scope.stopTTS();
                $scope.asr_results  = ev.results;
                console.log($scope.asr_results);
                $scope.target_text = $scope.asr_results[0][0].transcript;
                $scope.$apply();
                $scope.send();
            }
        };

        $scope.tts_listener = {
            onstart: function() {
                if($scope.dialog_state.current !== "start"
                        && $scope.preemptive
                        && !$scope.disable_followup)
                {
                    $scope.startASR();
                }
            },
            onend: function() {
                if($scope.dialog_state.current !== "start"
                        && !$scope.preemptive
                        && !$scope.disable_followup)
                {
                    $scope.startASR();
                }
            }
        };

        speech.setASRListener($scope.asr_listener.onstart, $scope.asr_listener.onend, $scope.asr_listener.onresult);
        speech.setTTSListener($scope.tts_listener.onstart, $scope.tts_listener.onend);

        $scope.startASR = function() {
            speech.startASR();
        };

        $scope.stopTTS = function(text) {
            speech.stopTTS();
        };

        $scope.startTTS = function(text) {
            $scope.answer = text;
            speech.startTTS(text);
        };

        $scope.send = function() {
            console.log("Sending state:");
            console.log($scope.dialog_state);
            ajax.send($scope.target_text,
                    $scope.dialog_state,
                    function(data) {
                        console.log(data);
                        $scope.startTTS(data.data.result.response);
                        $scope.dialog_state = data.data.result.state;

                        $scope.movies = data.data.result.db_result;
                    }, function(error) {
                        console.log(error);
                    });
        }
    }]);
})();
