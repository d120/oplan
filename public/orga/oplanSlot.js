
var module = angular.module("oplanSlot", ["angularGrid"]);
module.config(function($locationProvider) {
    //$locationProvider.html5Mode(true);
});
module.controller("OplanSlotCtrl", function($scope, oplanHttp, $filter, $routeParams, messageBar, $q) {
    if (!$scope.slotId && $routeParams.id) {
        $scope.slotId = $routeParams.id;
    }
    if (!$scope.slotId) { $scope.error = "Bitte Parameter id angeben"; return; }
    var columnDefs = [
        {headerName: "Kommentar", field: "kommentar", editable: true, width: 255, cellValueChanged: onEdit, checkboxSelection: true},
        {headerName: "Min.Plätze", field: "min_platz", editable: true, width: 75, cellValueChanged: onEdit},
        {headerName: "Raumnr. Präferenz", field: "praeferenz", editable: true, width: 130, cellValueChanged: onEdit},
        {headerName: "Raumnr. zugeteilt", field: "raum", cellRenderer: raumAuswahl, width: 130  }
    ];

    var frei = [];
    var rowData = [];

    $scope.gridOptions = {
        columnDefs: columnDefs,
        rowData: [],
        //dontUseScrolls: true, // because so little data, no need to use scroll bars,
        angularCompileRows: true,
        rowSelection: 'multiple',
        suppressRowClickSelection: true
    };
    function loadLines() {
        oplanHttp.doGet("ak/" + $scope.slotId).success(function(result) {
            $scope.gridOptions.rowData = result.raumbedarf;
            $scope.ak = result.ak;
            $scope.ak.von_dt = new Date($scope.ak.von);
            $scope.ak.bis_dt = new Date($scope.ak.bis);
            frei = result.frei;
            $scope.gridOptions.api.onNewRows();
        });
    }
    $scope.$watch('slotId' , loadLines);
    console.log($scope);
    $scope.copyPaste = "";
    $scope.$watch('gridOptions.rowData', function() {
        var out=[];
        for(var i=0; i<$scope.gridOptions.rowData.length; i++) out [i] = $scope.gridOptions.rowData[i].raum;
        $scope.copyPaste = out.join(";");
    });
    
    $scope.copyPasteApply = function() {
        "use strict";
        var it = $scope.copyPaste.split(/;/), rows = $scope.gridOptions.rowData;
        console.log($scope.copyPaste,it);
        if (it.length != rows.length) {alert("Item count mismatch");return;}
        for(var i=0; i<rows.length; i++) {
            (function(index) {
                oplanHttp.belegeRaum(it[i], rows[i].id)
                .success(function() {
                    rows[index].raum = it[index];
                })
                .error(function() {
                    rows[index].raum ="error";
                });
            })(i);
        }
    }
    
    $scope.addRow = function() {
        var rows = $scope.gridOptions.rowData;
        var last = rows[rows.length - 1];
        var komm = last.kommentar;
        komm = komm.substr(0, komm.length-1) + String.fromCharCode(komm.charCodeAt(komm.length-1)+1);
        oplanHttp.createBelegung($scope.ak.von, $scope.ak.bis, komm, last.zielgruppe)
        .success(function(data) {
            rows.push({
                id: data.id, kommentar: komm, min_platz: last.min_platz, praeferenz: "", raum: "", zielgruppe: last.zielgruppe
            });
            $scope.gridOptions.api.onNewRows();
            messageBar.show("success", "Neue Zeile angelegt.", 2500);
        });
    }
    
    function onEdit(params) {
        oplanHttp.updateBelegung(params.data.id, params.data);
    }
    
    function raumDetails(raum) {
        var week = moment($scope.ak.von_dt).isoWeek();
        window.open("#/"+$rootScope.vk+"/raumplan/" + raum+"?w="+week,"","width=850,height=730");
    }

    function raumAuswahl(params) {
        var html = '<span ng-click="startEditing()" ng-right-click="details()" class="linkstyle">{{data.'+params.colDef.field+' || "nicht zugewiesen"}}</span> ';
        params.$scope.details = function() {
            raumDetails(params.data.raum);
        }
        params.$scope.startEditing = function() {
            var date = $filter("date");
            console.log(params);
            var offset = $(params.eGridCell).offset();
            var edit = document.createElement("div");
            edit.className = "raumsel";
            edit.style.position="absolute";
            var elHeight = (frei.length+2)*35;
            var height = Math.min(elHeight, window.innerHeight - offset.top - 30);
            if (height < Math.min(elHeight,200)) {
              height = Math.min(elHeight,200); offset.top = window.innerHeight - height - 30;
            }
            edit.style.top = offset.top+"px";
            edit.style.left = offset.left+"px";
            edit.style.height = height+"px";
            document.body.appendChild(edit);
            setTimeout(function() {
                $(document.body).one("click", function(e) {
                    if (e.target.getAttribute("data-delete") !== null) {
                        oplanHttp.doDELETE("belegung/" + params.data.id, { delete: true })
                        .success(function() {
                            loadLines(); messageBar.show("success", "Zeile wurde gelöscht.", 2500);
                        });
                    } else if (e.target.getAttribute("data-raumnr") !== null) {
                        params.$scope.$apply(function() {
                            var oldValue = params.data.raum;
                            params.data.raum = e.target.getAttribute("data-raumnr");
                            params.eGridCell.style.background="#ccc";
                            oplanHttp.belegeRaum(params.data.raum, params.data.id)
                            .success(function() {
                                params.eGridCell.style.background="green";
                                setTimeout(function() { params.eGridCell.style.background=""; },200);
                            })
                            .error(function(data) {
                                params.eGridCell.style.background="";
                                params.data.raum = oldValue;
                                if (data && data.error == "overlaps") {
                                    alert("Konnte Raum nicht zuweisen, da schon belegt: "+data.error_details[0].kommentar+" - "+data.error_details[0].von);
                                }
                            });
                            
                        });
                    }
                    edit.parentElement.removeChild(edit);
                });
                $(edit).on("contextmenu", function(e) {
                    var nr = e.target.getAttribute("data-raumnr");
                    if (nr) {
                        raumDetails(nr);
                        return false;
                    }
                });
            },0)
            for(var k in frei) {
                var d = frei[k];
                var uhr = date(new Date(d.von), "HH:mm")+"-"+date(new Date(d.bis),"HH:mm");
                $("<div>").attr("data-raumnr", d.raum_nummer).toggleClass("belegt",!!d.belegt)
                .text(d.raum_nummer+" ("+uhr+")"+(d.belegt?" - "+d.belegt:"")).appendTo(edit);
            }
            $("<div>").attr("data-raumnr", "").text("(nicht zugewiesen)").appendTo(edit);
            $("<div>").attr("data-delete", "").text("(Zeile löschen)").appendTo(edit);

        }
        return html;
    }
    
    
    $scope.toggleSelection = function() {
        var xx=$scope.gridOptions.api;
        xx.forEachInMemory(function(node) {
          if (xx.isNodeSelected(node)) xx.deselectNode(node);
          else xx.selectNode(node, true);
        });
    }
    
    $scope.gridOptions.selectionChanged = function() {
        $scope.multiselect.show = ($scope.gridOptions.selectedRows.length > 0);
    }
    
    $scope.multiselect = {
        show: false,
        action: "",
        value: ""
    };
    $scope.runMultiselectAction = function() {
        var promises = [];
        var xx=$scope.gridOptions.api;
        xx.forEachInMemory(function(node) {
          if (xx.isNodeSelected(node)) {
            console.log("selected: "+node.data.id+" "+node.data.kommentar+" --- doing action "+$scope.multiselect.action+"=", $scope.multiselect.value);
            switch($scope.multiselect.action) {
            case "delete":
              promises.push( oplanHttp.doDELETE("belegung/" + node.data.id, { delete: true }) );
              break;
            case "set_zielgruppe":
              node.data.zielgruppe = $scope.multiselect.value;
              promises.push( oplanHttp.updateBelegung(node.data.id, node.data) );
              break;
            case "set_min_platz":
              node.data.min_platz = $scope.multiselect.value;
              promises.push( oplanHttp.updateBelegung(node.data.id, node.data) );
              break;
            }
          }
        });
        $q.all(promises).then(function(ok) {
          console.log(ok);
          loadLines(); messageBar.show("success", ""+ok.length+" Aktionen durchgeführt", 2500);
        }, function(err) {
          console.log(err);
          loadLines();// messageBar.show("error", "Fehler: "+err, 2500);
        });
      
    }
    
    $("#belegunggrid").css('height',window.innerHeight - 200 + 'px');
    

})


module.directive('sizewithwindow', function ($window) {
    return function (scope, element) {
        var w = angular.element($window);
        var onResize = function () {
            scope.windowHeight = window.innerHeight;
            scope.windowWidth = window.innerWidth;
        };
        onResize();
        w.bind('resize', onResize);
        scope.$on('$destroy', function() {
            w.unbild('resize', onResize);
        });
    }
});