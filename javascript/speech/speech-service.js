(function(angular) {
    "use strict";

    var app = angular.module('speech-module', []);

    app.service('speech-service', [ '$log', '$mdToast', function($log, $mdToast){

        var context = this;

        context.recognizing = false;
        context.TTS = new SpeechSynthesisUtterance();
        context.ASR = new webkitSpeechRecognition();

        this.ASR.lang='en-US';

        this.tts_listener = {
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

        this.setTTSListener = function(onstart, onend) {
            context.tts_listener = {
                onstart: onstart,
                onend: onend
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
            console.log('Starting Speech Recognition');
            context.ASR.start();
        };

        this.startTTS = function(text){
            var voices = window.speechSynthesis.getVoices();
            context.TTS.lang = 'en-GB';
            context.TTS.text =  text;
            speechSynthesis.speak(context.TTS);
        };

        this.ASR.onstart = function() {
            console.log('onstart Speech Recognition');
            context.recognizing = true;
            context.asr_listener.onstart();
        };
        this.ASR.onend = function() {
            console.log('onend Speech Recognition');
            context.recognizing = false;
            context.asr_listener.onend();
        };
        this.ASR.onresult = function(ev) {
            console.log('onresult Speech Recognition');
            context.asr_listener.onresult(ev);
        };

        this.TTS.onend = function(ev) {
            console.log('onend TTS');
            context.tts_listener.onend(ev);
        }


    }]);

})(window.angular);
