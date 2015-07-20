angular.module('httpIndicator', [])
.factory('messageBar', function() {
  var self = {
    show: function(className, text, interval) {
      var id = "loadingWidget_" + className;
      if ($('#'+id).length == 0)
        $('<div id="'+id+'" class="messageBar"></div>').prependTo("body");
      $('#'+id).text(text).addClass(className).slideDown();
      if (interval) setInterval(function() { self.hide(className); }, interval);
    },
    hide: function(className) {
      $("#loadingWidget_"+className).slideUp();
    }
  };
  return self;
})
.config(['$httpProvider', '$provide', function ($httpProvider, $provide) {
  $provide.factory('httpIndicatorProvider', function($q, $injector, messageBar, $rootScope) {
    var $http;
    return {
      // on request start
      'request': function(config) {
        messageBar.show('loading', 'Eile mit Weile...');
        config.headers.Authorization = 'Basic '+$rootScope.auth;
        return config;
      },

      // on success
      'response': function(response) {
        $http = $http || $injector.get('$http');
        if($http.pendingRequests.length < 1) {
            messageBar.hide('loading');
        }
        console.log("response:",response);
        return response;
      },

      // optional method
      'responseError': function(rejection) {
        $http = $http || $injector.get('$http');
        console.log("HTTP Error: ",rejection);
        if($http.pendingRequests.length < 1) {
            messageBar.hide('loading');
        }

        if (rejection.data) {
            messageBar.show("error", "Fehler: " + rejection.data.error, 3000);
        } else if (rejection.status) {
            messageBar.show("error", "Allgemeiner Fehler: " + rejection.status + " " + rejection.statusText, 3000);
        } else {
            messageBar.show("error", "Exception: " + rejection, 3000);
        }
        return $q.reject(rejection);
      }
    };
  });

  $httpProvider.interceptors.push('httpIndicatorProvider');
}]);

