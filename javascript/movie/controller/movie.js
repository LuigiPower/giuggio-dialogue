(function(){
    "use strict";
    var app = angular.module('speech-module');

    app.controller("lusMovieController", ['$scope', 'ajax-service', function($scope, ajax){

        $scope.scrapeMissingInfo = function() {
            ajax.scrape($scope.movie.movie_imdb_link,
                    function(data) {
                        console.log("Successful scraping");
                        console.log(data);
                        $scope.movie.poster = data.data.result.poster;
                    }, function(error) {
                        console.log("Error while scraping " + $scope.movie.movie_imdb_link);
                    });
        };

        $scope.scrapeMissingInfo();

    }]);
})();
