(function(angular) {
    "use strict";

    var app = angular.module('utility-module', []);

    app.service('utility-service', [ '$log', '$mdToast', function($log, $mdToast){

        var context = this;
        this.precision = 2;
        this.requested = false;
        /**
         * current_timeout
         * used to cancel the last scheduled timeout
         */
        // this.current_timeout;

        this.average = function(values) {
            return values.reduce(function(sum, value) {
                  return sum + value;
            }, 0) / values.length;
        }

        this.computeStatistics = function(value_array)
        {
            console.log(value_array);
            var mean = context.average(value_array);
            var diff = value_array.map(function(value) {
                var difference = value - mean;
                return difference * difference;
            });

            var variance = context.average(diff);
            var standard_deviation = Math.sqrt(variance);

            return { mean: mean.toFixed(context.precision), variance: variance.toFixed(context.precision), stdev: standard_deviation .toFixed(context.precision)};
        };

        this.showToast = function(text)
        {
            $mdToast.show(
                    $mdToast.simple()
                    .content(text)
                    .position('bottom')
                    .hideDelay(3000)
                    );
        };

        this.isObjectEmpty = function(obj) {
            for(var prop in obj) {
                if(obj.hasOwnProperty(prop))
                    return false;
            }

            return true;
        };

        this.polarToCartesian = function(centerX, centerY, radius, angleInDegrees) {
            var angleInRadians = (angleInDegrees-90) * Math.PI / 180.0;

            return {
                x: centerX + (radius * Math.cos(angleInRadians)),
                y: centerY + (radius * Math.sin(angleInRadians))
            };
        };

        this.describeArc = function(x, y, radius, startAngle, endAngle){

            var start = context.polarToCartesian(x, y, radius, endAngle);
            var end = context.polarToCartesian(x, y, radius, startAngle);

            var largeArcFlag = endAngle - startAngle <= 180 ? "0" : "1";

            var d = [
                "M", start.x, start.y,
                "A", radius, radius, 0, largeArcFlag, 0, end.x, end.y
            ].join(" ");

            return d;
        };

}]);

app.directive('stringToNumber', function() {
    return {
        require: 'ngModel',
        link: function(scope, element, attrs, ngModel) {
            ngModel.$parsers.push(function(value) {
                return '' + value;
            });
            ngModel.$formatters.push(function(value) {
                return parseFloat(value, 10);
            });
        }
    };
});

})(window.angular);
