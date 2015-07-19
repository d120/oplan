angular.module("oplanApp", ["ngRoute", "oplanRaumListe", "oplanTimetable", "oplanSlot", "oplanBackend"])

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
        //redirectTo: '/login'
      });
  }])

.run(['$location', '$rootScope', 
  function($location, $rootScope) {
    $rootScope.$on('$routeChangeSuccess', function (event, current, previous) {
        $rootScope.title = current.$$route.title;
    });
    
    $rootScope.gotoRoomKey = function(e) {
        if (e.keyCode == 13) {
            $location.path("/raumplan/" + e.target.value).search("w", 41);
        }
    }
  }])
  
.controller('OplanHomeCtrl', function($scope) {
    
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

;