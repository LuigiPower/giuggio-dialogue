(function(angular) {
    "use strict";

    var app = angular.module('widget-module', ['utility-module']);

    app.service('widget-service', ['$mdDialog', '$mdToast', 'utility-service', function ($mdDialog, $mdToast, utility) {

        var context = this;

        this.showDialogDismissable = function(ev, dismissable, template, controllerInit, success, failure){
            $mdDialog.show({
                controller: 'WidgetController',
                templateUrl: template,
                parent: angular.element(document.body),
                targetEvent: ev,
                clickOutsideToClose: dismissable,
                locals : {
                    init : controllerInit
                }
            })
            .then(success, failure);
        }

        this.showDialog = function(ev, template, controllerInit, success, failure) {
            context.showDialogDismissable(ev, true, template, controllerInit, success, failure);
        };

        this.hideDialog = function() {
            $mdDialog.hide();
        }

        this.showToast = function(message) {
            var pinTo = "bottom right";

            $mdToast.show(
                    $mdToast.simple()
                    .textContent(message)
                    .position(pinTo)
                    .hideDelay(3000)
                    );
        };

    }]);

    app.controller('WidgetController', ['$scope', '$mdDialog', 'authentication-service', 'ajax-service', 'init', function($scope, $mdDialog, auth, ajax, init) {

        $scope.hide = function() {
            console.log("Pressed hide in dialog");
            $mdDialog.hide();
        };
        $scope.cancel = function() {
            console.log("Pressed cancel in dialog");
            $mdDialog.cancel();
        };
        $scope.answer = function(answer) {
            console.log("Pressed OK in dialog");
            $mdDialog.hide(answer);
        };

        init($scope, $mdDialog, auth, ajax);
    }]); //controller

})(window.angular);
