(function(){
    "use strict";
    var app = angular.module('speech-module');

    app.directive("lusMovielist", ['ajax-service', function(ajax){
        return{
            restrict: 'E',
            templateUrl: "template/movielist.html",
            controller: "lusMovielistController",
            scope: {
                movies: '=movies'
            }
        };
    }]);
})();
