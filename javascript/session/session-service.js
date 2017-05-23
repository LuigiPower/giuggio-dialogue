(function(){
    "use strict";

    var app = angular.module('session-module', ['utility-module']);

    app.service('session-service', [ 'utility-service', function(utility) {
        var context = this;

        this.defaultTitle = "CTF Automazioni";
        this.title = this.defaultTitle;

        this.screenStack = [];
        this.listenerList = [];

        this.screen = "none";

        this.isNowLoading = false;

        this.showLoading = function()
        {
            console.log("show loading");
            this.isNowLoading = true;
        }

        this.hideLoading = function()
        {
            console.log("hide loading");
            this.isNowLoading = false;
        }

        this.isLoading = function()
        {
            console.log("is loading");
            return context.isNowLoading;
        }

        this.clearStack = function()
        {
            context.listenerList = [];
            context.screenStack = [];
            context.screen = "none";
        }

        this.goTo = function(screen, addToBackstack, clear)
        {
            console.log("Going to " + screen);
            console.log("Stack is " + context.screenStack);

            var from = context.screen;

            // TODO customer based title
            if(screen === "azienda_c")
            {
                context.title = "Whirlpool";
            }
            else
            {
                context.title = context.defaultTitle;
            }

            if(addToBackstack === undefined)
                addToBackstack = true;

            if(clear === undefined)
                clear = false;

            if(clear)
            {
                context.screenStack = context.screenStack.splice(0, 1);
            }

            if(addToBackstack)
            {
                context.screenStack.push("" + context.screen);
            }
            context.screen = screen;

            for(var i = 0; i < context.listenerList.length; i++)
            {
                var listener = context.listenerList[i];

                if(listener.onScreenChange)
                {
                    listener.onScreenChange(screen);
                }
            }
            console.log("After: Stack is " + context.screenStack);
            console.log("After: Screen is " + context.screen);
        };

        this.goToStart = function()
        {
            while(context.screenStack.length > 1)
            {
                context.goBack();
            }
        }

        this.goBack = function()
        {
            context.screen = context.screenStack.pop();

            for(var i = 0; i < context.listenerList.length; i++)
            {
                var listener = context.listenerList[i];

                if(listener.onBack)
                {
                    listener.onBack(context.screen, context.screenStack[context.screenStack.length-1]);
                }

                if(listener.oneTime)
                {
                    context.listenerList.splice(i, 1);
                }
            }
        };

        this.addListener = function(listener)
        {
            context.listenerList.push(listener);
        }

    }]);

})();
