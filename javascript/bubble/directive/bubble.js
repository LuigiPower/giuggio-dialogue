(function(){
    "use strict";
    var app = angular.module('speech-module');

    app.directive("speechBubble", ['ajax-service', function(ajax){
        return{
            restrict: 'E',
            templateUrl: "template/bubble.html",
            controller: "speechBubbleController",
            scope: {
                message: '=message'
            }
        };
    }]);
})();
