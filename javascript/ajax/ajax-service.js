(function(angular) {
    "use strict";

    var app = angular.module('ajax-module', [], function($httpProvider) {
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

        /**
         * The workhorse; converts an object to x-www-form-urlencoded serialization.
         * @param {Object} obj
         * @return {String}
         */
        var param = function(obj) {
            var query = '', name, value, fullSubName, subName, subValue, innerObj, i;

            for(name in obj) {
                value = obj[name];

                if(value instanceof Array) {
                    for(i=0; i<value.length; ++i) {
                        subValue = value[i];
                        fullSubName = name + '[' + i + ']';
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += param(innerObj) + '&';
                    }
                }
                else if(value instanceof Object) {
                    for(subName in value) {
                        subValue = value[subName];
                        fullSubName = name + '[' + subName + ']';
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += param(innerObj) + '&';
                    }
                }
                else if(value !== undefined && value !== null)
                    query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
            }

            return query.length ? query.substr(0, query.length - 1) : query;
        };

        // Override $http service's default transformRequest
        $httpProvider.defaults.transformRequest = [function(data) {
            return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
        }];
    });

    app.service('ajax-service', ['$http', 'utility-service', 'session-service', function($http, utility, session){
        var context = this;

        this.defaultHeader = {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'};
        this.GET = "GET";
        this.POST = "POST";

        /**
         * Script URLs
         * TODO set these correctly
         */
        this.script = "php/dialog.php";
        this.scrapescript = "php/scrape.php";

        /**
         * Contexts
         */
        this.USER = "user";
        this.MANUFACTURER = "manufacturer";
        this.CUSTOMER = "customer";
        /** END CONTEXTS **/

        /**
         * execute a request
         * headers - headers of request
         * method - method of request
         * params - parameters of request (add action: parameter)
         * success - callback function
         */
        this.request = function(script, method, params, success, failure){
            console.log("request is");
            console.log(script);
            console.log(method);
            console.log(params);
            console.log(success);
            console.log(failure);
            session.showLoading();
            if(method === "POST")
            {
                $http.post(script, params).then(success, failure);
            }
            else
            {
                var conf_obj = {
                    headers: context.defaultHeader,
                    url: script,
                    method: method,
                    params: params,
                    data: params //TODO se i post o context.GET non funzionano qui e' l'errore (params e' per GET, data e' per context.POST)
                };

                $http(conf_obj).then(
                        function(data) {
                            session.hideLoading();
                            success(data);
                        }, function(data) {
                            session.hideLoading();
                            failure(data);
                        });
            }
        };

        this.send = function(text, state, success, failure) {
            console.log("Sending request " + text);
            context.request(context.script, context.POST, { utterance: text, dialog_state: JSON.stringify(state) }, success, failure);
        };

        this.scrape = function(url, success, failure) {
            console.log("Scraping " + url);
            context.request(context.scrapescript, context.POST, { toscrape: url }, success, failure);
        };

    }]);
})(window.angular);
