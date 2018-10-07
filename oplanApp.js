angular.module("oplanApp", ["ngRoute", "oplanRaumListe", "oplanTimetable", 
                            "oplanSlot", "oplanBackend", "httpIndicator"])

.config(['$routeProvider',
  function($routeProvider) {
    $routeProvider.
      when('/', {
        title: 'Oplan',
        templateUrl: 'partials/home.html',
        controller: 'OplanHomeCtrl'
      }).
      when('/raumliste', {
        title: 'Raumliste',
        templateUrl: 'partials/raumliste.html',
        controller: 'OplanRaumListeCtrl'
      }).
      when('/raumliste/tucan', {
        title: 'Raumliste TUCaN-Style',
        templateUrl: 'partials/raumlistetucan.html',
        controller: 'OplanTucanRaumListeCtrl'
      }).
      when('/raumliste/kleingruppe', {
        title: 'Kleingruppenliste',
        templateUrl: 'partials/kleingruppen.html',
        controller: 'OplanKleingruppenlisteCtrl'
      }).
      when('/slot/:id', {
        title: 'Slot',
        templateUrl: 'partials/slot.html',
        controller: 'OplanSlotCtrl'
      }).
      when('/stundenplan/:gruppe', {
        title: 'Stundenplan',
        templateUrl: 'partials/timetable.html',
        controller: 'OplanTimetableCtrl',
        reloadOnSearch: false
      }).
      when('/raumplan/:raum', {
        title: 'Raumplan',
        templateUrl: 'partials/room.html',
        controller: 'OplanRoomCtrl',
        reloadOnSearch: false
      }).
      otherwise({
        title: '404',
        templateUrl: 'partials/404.html'
        //redirectTo: '/login'
      });
  }])

.run(['$location', '$rootScope', 'oplanHttp',
  function($location, $rootScope, oplanHttp) {
    $rootScope.$on('$routeChangeSuccess', function (event, current, previous) {
        if (current.$$route) $rootScope.title = current.$$route.title;
    });
    
    $rootScope.gotoRoomKey = function(e) {
        if (e.keyCode == 13) {
            e.target.value = e.target.value.toUpperCase();
            $location.path("/raumplan/" + e.target.value).search("w", $rootScope.defaultWeek);
        }
    }

    if (localStorage.auth) {
      $rootScope.auth = localStorage.auth;
    }

    oplanHttp.listStundenplans().success(function(ok) {
      $rootScope.stundenplanlist = ok;      
    })
    
    $rootScope.defaultWeek = CALENDAR_DEFAULT_WEEK;
  }])
  
.controller('OplanHomeCtrl', function($scope, $rootScope, oplanHttp) {
    $scope.username = $rootScope.auth ? atob($rootScope.auth).split(/:/)[0] : '';
    $scope.login = function() {
      $rootScope.auth = btoa($scope.username + ":" + $scope.password);
      oplanHttp.doGet("login", {}).then(function(ok) {
        localStorage.auth = $rootScope.auth;
      }, function(err) {
        $rootScope.auth = null;
        setTimeout(function() {
          $("#login_pw").focus().select();
        },1)
      });
      
    }
    $scope.logout = function() {
      $rootScope.auth = null;
      localStorage.auth = "";
    }
    
  })

.directive('ngRightClick', function($parse) {
    return function(scope, element, attrs) {
        var fn = $parse(attrs.ngRightClick);
        element.bind('contextmenu', function(event) {
            scope.$apply(function() {
                event.preventDefault();
                fn(scope, {$event:event});
            });
        });
    };
})



.factory('mwContextMenu', function($http) {
    return function(event, menuItems) {console.log(event)
        var menu = $("<div class='raumsel'></div>");
        menu.css({ top: event.pageY + "px", left: event.pageX + "px" });
        for(var k in menuItems) {
            var item = $("<div>"+k+"</div>").appendTo(menu);
            item.click(menuItems[k]);
        }
        $(document.body).append(menu);
        setTimeout(function() {
          $(document).one("click", function(e) {
            menu.remove(); e.preventDefault();
          })
          $(document).one("contextmenu", function(e) {
            menu.remove(); e.preventDefault();
          })
        },1)
    }
});
;
