(function(){
    "use strict";

    console.log("Creating app");
    var app = angular.module('giuggio-dialogue', [
            'ngMaterial', 'ajax-module', 'utility-module',
            'session-module', 'widget-module', 'speech-module'
    ]);

    app.config(function($mdThemingProvider){
        $mdThemingProvider.theme('default')
            .primaryPalette('amber', {
                'default':'700',
                'hue-1': '100', // use shade 100 for the <code>md-hue-1</code> class
                'hue-2': '600' // use shade 600 for the <code>md-hue-2</code> class
            });
    });

    app.directive('stopEventPropagation', function () {
        return {
            restrict: 'A',
            link: function (scope, element) {
                element.bind('click', function (event) {
                    event.stopPropagation();
                });
            }
        };
    });

    app.directive('selectOnClick', ['$window', function ($window) {
        return {
            restrict: 'A',
            link: function (scope, element, attrs) {
                element.on('click', function () {
                    if (!$window.getSelection().toString()) {
                        //Required for mobile Safari
                        this.setSelectionRange(0, this.value.length)
                    }
                });
            }
        };
    }]);

    app.filter('multiple', function() {
        return function(input) {
            var splitted = input.split("|");
            var text = "";
            for(var i in splitted)
            {
                text += splitted[i];
                if(i == splitted.length-2)
                {
                    text += " and ";
                }
                else if(i != splitted.length-1)
                {
                    text += ", ";
                }
            }
            return text;
        };
    })
})();
