(function(){
    "use strict";
    var app = angular.module('speech-module');

    app.controller("giuggioSpeechController", ['$scope', 'ajax-service', 'widget-service', 'session-service', 'speech-service', function ($scope, ajax, widget, session, speech){

        $scope.target_text = "";
        $scope.preemptive = false;
        $scope.disable_followup = false;
        $scope.asr_confidence = 1;

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
                $scope.asr_confidence = $scope.asr_results[0][0].confidence;
                $scope.$apply();
                $scope.send();
            }
        };

        $scope.tts_listener = {
            onstart: function() {
                if(speech.dialog_state.current !== "start"
                        && $scope.preemptive
                        && !$scope.disable_followup)
                {
                    $scope.startASR();
                }
            },
            onend: function() {
                if(speech.dialog_state.current !== "start"
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

        $scope.getSpeechStack = function() {
            return speech.speechStack;
        }

        $scope.sendButton = function() {
            $scope.asr_confidence = 1;
            $scope.send();
        };

        $scope.send = function() {
            $scope.stopTTS();
            console.log("Sending state:");
            console.log(speech.dialog_state);
            speech.pushMessage($scope.target_text, true);
            //speech.pushMessage("...", false);
            ajax.send($scope.target_text,
                    speech.dialog_state,
                    $scope.asr_confidence,
                    function(data) {
                        console.log(data);
                        $scope.startTTS(data.data.result.response);
                        speech.dialog_state = data.data.result.state;

                        $scope.movies = data.data.result.db_result;
                    }, function(error) {
                        console.log(error);
                    });
        }

        $scope.startTTS("Hello and welcome to the movie database, how can I help you?");
    }]);
})();
