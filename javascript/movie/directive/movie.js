(function(){
    "use strict";
    var app = angular.module('speech-module');

    app.directive("lusMovie", ['ajax-service', function(ajax){
        return{
            restrict: 'E',
            templateUrl: "template/movie.html",
            controller: "lusMovieController",
            scope: {
                movie: '=movie'
            }
        };
    }]);
})();
