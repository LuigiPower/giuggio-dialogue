(function(angular) {
    "use strict";

    var app = angular.module('speech-module', []);

    app.service('speech-service', [ '$log', '$mdToast', function($log, $mdToast){

        var context = this;

        context.recognizing = false;
        context.TTS = new SpeechSynthesisUtterance();
        context.ASR = new webkitSpeechRecognition();

        this.ASR.lang='en-US';

        this.asr_listener = {
            onstart: function(){},
            onend: function(){},
            onresult: function(){}
        };

        this.setASRListener = function(onstart, onend, onresult) {
            context.asr_listener = {
                onstart: onstart,
                onend: onend,
                onresult: onresult
            };
        }


        /*
        this.ASR.onresult = function(event) {
            console.log(event);
            best_transcript=event.results[0][0].transcript;
            $("#context.ASRDiv").html(best_transcript);
        };
        */


        this.startASR = function() {
            context.ASR.start();
            console.log('Starting Speech Recognition');
        };

        this.startTTS = function(text){
            var voices = window.speechSynthesis.getVoices();
            context.TTS.lang = 'en-GB';
            context.TTS.text =  text;
            speechSynthesis.speak(context.TTS);
        };

        this.ASR.onstart = function() {
            context.recognizing = true;
            context.asr_listener.onstart();
        };
        this.ASR.onend = function() {
            context.recognizing = false;
            context.asr_listener.onend();
        };
        this.ASR.onresult = function(ev) {
            context.asr_listener.onresult(ev);
        };


    }]);

})(window.angular);
