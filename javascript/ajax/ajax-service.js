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

    app.service('ajax-service', ['$http', 'utility-service', 'session-service', 'authentication-service', function($http, utility, session, auth){
        var context = this;

        this.defaultHeader = {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'};
        this.GET = "GET";
        this.POST = "POST";

        /**
         * Actions for Hercules web service
         */
        this.send_notice = "send_notice";
        this.login = "login";
        this.get_client_settings = "get_client_settings";
        this.get_customer_list = "get_customer_list";
        this.get_customer_vista = "get_customer_vista";
        this.get_widget_list = "get_widget_list";
        this.get_manufacturer_log = "get_manufacturer_log";
        this.get_documentation = "get_documentation";
        this.get_notification_list = "get_notification_list";
        this.get_customer_production_line_list = "get_customer_production_line_list";
        this.get_zeus_resource = "get_zeus_resource";

        this.confirm_notification = "confirm_notification";

        this.get_production_line_var_list = "get_production_line_var_list";
        this.update_production_line_var = "update_production_line_var";
        this.delete_production_line_var = "delete_production_line_var";
        this.create_production_line_var = "create_production_line_var";

        this.get_user_list = "get_user_list";
        this.create_user = "create_user";
        this.update_user = "update_user";
        this.delete_user = "delete_user";

        this.create_widget = "create_widget";
        this.update_widget = "update_widget";
        this.delete_widget = "delete_widget";
        /** END HERCULES ACTIONS **/

        /**
         * Actions for Hades web service
         */
        this.get_data = "get_data";
        /** END HADES ACTIONS **/

        /**
         * Script URLs
         * TODO set these correctly
         */
        this.hercules_request = "http://138.197.78.87/hercules/";
        this.hades_request = "http://138.197.78.87/hades/";

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

        this.doHadesAction= function(action, data, success, failure) {
            console.log("Hades actin");
            data["ACTION"] = action;
            data["CONTEXT"] = auth.hades_context;

            data["id_user"] = auth.loggedInUser.id_user;
            data["token"] = auth.loggedInUser.token;

            return context.request(context.hades_request, context.POST,
                    {
                        REQUEST_DATA: JSON.stringify(data)
                    },
                    function(data){ console.log(data); if(success){success(data);} },
                    function(data){ console.log(data); if(failure){failure(data);} });
        };

        this.doAction = function(action, data, success, failure) {
            data["ACTION"] = action;
            data["CONTEXT"] = auth.hercules_context;

            if(!data.id_customer)
                data["id_customer"] = auth.loggedInUser.id_customer;

            if(!data.id_manufacturer)
                data["id_manufacturer"] = auth.loggedInUser.id_manufacturer;

            data["id_user"] = auth.loggedInUser.id_user;
            data["token"] = auth.loggedInUser.token;
            data["id_language"] = auth.loggedInUser.id_language;
            data["production_lines"] = undefined;

            return context.request(context.hercules_request, context.POST,
                    {
                        REQUEST_DATA: JSON.stringify(data)
                    },
                    function(data){ console.log(data); if(success){success(data);} },
                    function(data){ console.log(data); if(failure){failure(data);} });
        };

    }]);
})(window.angular);
