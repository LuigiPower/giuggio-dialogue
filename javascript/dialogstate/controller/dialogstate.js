(function(){
    "use strict";
    var app = angular.module('speech-module');

    app.controller("speechDialogstateController", ['$scope', 'ajax-service', 'speech-service', function($scope, ajax, speech){

        /**
        {"intent":"movie","fields":{},"probableIntents":{"0":{"0":"movie","1":0.88229324847445},"1":{"0":"movie_count","1":0.11417410064017},"2":{"0":"revenue","1":0.0015112308965059},"3":{"0":"release_date","1":0.0010693216569984}},"probableFields":{"movie.name:0":"1999"},"confirmedFields":{},"current":"confirm_slu","askedField":{"key":"movie.name:0","value":"1999","negated":false,"confirmed":false,"ignored":false,"concept_fixed":false},"operand":">","countResults":true}
        */
        $scope.getVar = function(key)
        {
            return speech.dialog_state && speech.dialog_state[key];
        }

    }]);
})();
