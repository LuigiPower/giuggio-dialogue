(function(){
    "use strict";
    var app = angular.module('speech-module');

    app.directive("speechDialogstate", ['ajax-service', function(ajax){
        return{
            restrict: 'E',
            templateUrl: "template/dialogstate.html",
            controller: "speechDialogstateController",
            scope: {

            }
        };
    }]);
})();
