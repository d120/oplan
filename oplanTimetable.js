angular.module('oplanTimetable', ['ui.calendar'])

.controller('OplanTimetableCtrl', ['$scope', '$routeParams', 'oplanHttp', 'uiCalendarConfig', '$location', '$interval',
  function($scope, $routeParams, oplanHttp, uiCalendarConfig, $location, $interval) {
    $scope.gruppe = $routeParams.gruppe;
    /* config object */
    $scope.calendar = {
        height: 650,
        firstDay: 1,
        weekNumbers: true,
        editable: true,
        scrollTime: '07:30:00',
        timeFormat: 'H:mm',
        snapDuration: '0:05',
        defaultView: 'agendaWeek',
        header:{
          left: 'agendaWeek agendaDay',
          center: 'title',
          right: 'today prev,next'
        },
        viewRender: onRenderView,
        eventClick: onEventClick
    };
    
    function onRenderView(view, element) {
        $location.search("w", view.start.isoWeek()).replace();
    }
    
    function onEventClick(event, jsEvent, view) {
        $scope.slotId = event.id;
    }
    
    $scope.closeDetails = function() {
        $scope.slotId = null;
    }
    
    function eventLoader (start, end, timezone, callback) {
      oplanHttp.doGet("stundenplan", { format: "json", w: moment(start).format("ww"), g: $routeParams.gruppe })
      .success(function(result) {
        
        callback(result.map(function(x) {
          x.start = new Date(x.von);
          x.end = new Date(x.bis);
          x.title = x.kommentar + ' (' + x.anz + ')';
          if (x.typ == 'ok') x.color = 'green';
          return x;
        }));
      });
    }
    
    $scope.eventSources = [ eventLoader ];
    
    if ($routeParams.w) {
      var d = moment($routeParams.w, "ww").add(1,'d');
      console.log(d);
      $scope.calendar.defaultDate = d;
    }
    
  }])
  
.controller('OplanRoomCtrl', ['$scope', '$routeParams', 'oplanHttp', 'uiCalendarConfig', '$location',
  function($scope, $routeParams, oplanHttp, uiCalendarConfig, $location) {
    $scope.room = $routeParams.raum;
    /* config object */
    $scope.calendar = {
        height: 650,
        firstDay: 1,
        weekNumbers: true,
        editable: true,
        scrollTime: '07:30:00',
        timeFormat: 'H:mm',
        snapDuration: '0:05',
        defaultView: 'agendaWeek',
        selectable: true,
        selectHelper: true,
        select: onSelectTimerange,
        eventDrop: onEventChange,
        eventResize: onEventChange,
        header:{
          left: 'agendaWeek agendaDay',
          center: 'title',
          right: 'today prev,next'
        },
        viewRender: onRenderView
    };
    
    function onRenderView(view, element) {
        $location.search("w", view.start.isoWeek()).replace();
        $scope.calStart = view.start.toDate();
    }
    
    var lastDesc = "";
    function onSelectTimerange(start, end) {
        var desc = prompt("Eintragen, dass "+$scope.room+" von "+start.format("HH:mm")+" bis "+end.format("HH:mm")+" frei ist?\n\nKommentar:", lastDesc);
        if (desc === null) return;
        lastDesc = desc;
        
        oplanHttp.setRaumFrei(null, $scope.room, start, end, desc, "???")
        .success(function() {
            uiCalendarConfig.calendars.timetable.fullCalendar('unselect');
            uiCalendarConfig.calendars.timetable.fullCalendar('refetchEvents');
        })
        .error(function(data) {
            alert("Allgemeiner Fehler");
        });
    }
    
    function onEventChange(event, delta, revertFunc, jsEvent, ui, view ) {
      if (event.typ == "frei") {
        var desc = prompt("Raum-Frei-Eintragung ändern? Raum: "+$scope.room+" von "+event.start.format("HH:mm")+" bis "+event.end.format("HH:mm")+"\n\nKommentar:", event.title);
        if (desc === null) return;
        
        oplanHttp.setRaumFrei(event.id, $scope.room, event.start, event.end, desc, "???")
        .success(function() {
            uiCalendarConfig.calendars.timetable.fullCalendar('unselect');
            uiCalendarConfig.calendars.timetable.fullCalendar('refetchEvents');
        })
        .error(function(data) {
            revertFunc();
            alert("Allgemeiner Fehler");
        });
      } else {
        revertFunc();
      }
    }

    
    function eventLoader (start, end, timezone, callback) {
      oplanHttp.doGet("stundenplan", { format: "json", w: moment(start).format("ww"), raum: $routeParams.raum })
      .success(function(result) {
        
        callback(result.map(function(x) {
          x.start = new Date(x.von);
          x.end = new Date(x.bis);
          x.title = x.kommentar;
          switch(x.typ) {
            case "ok": x.color = "darkgreen"; break;
            case "wunsch": x.color = "orange"; break;
            case "frei": x.color = "#22ee55"; x.textColor="green";
             if(x.status=="tucan"){ x.startEditable=false; x.durationEditable=false;}
             break;
            case "block": x.color = "red"; x.rendering="background"; x.startEditable=false; x.durationEditable=false; break;
              break;
          }
          return x;
        }));
      });
    }
    
    $scope.eventSources = [ eventLoader ];
    
    if ($routeParams.w) {
      var d = moment().isoWeek($routeParams.w-1).day("Tuesday");
      console.log(d);
      $scope.calendar.defaultDate = d;
    }
    
    oplanHttp.doGet("raum", { nummer: $scope.room })
    .success(function(data) {
        $scope.raumInfo = data.info;
    });
    
    $scope.updateAusVerw = function() {
        oplanHttp.doPost("parse_timetable.php", { start: $scope.calStart.getTime()/1000, 
          nummer: $scope.room })
        .success(function(data) {
          if(data.success) {
              uiCalendarConfig.calendars.timetable.fullCalendar('refetchEvents');
          } else {
              alert(data.error);
          }
        })
        .error(function(data) {
            alert("Allgemeiner Fehler");
        });
    }
    
  }])
;
