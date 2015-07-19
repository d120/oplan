
var module = angular.module("oplanSlot", ["angularGrid"]);
module.config(function($locationProvider) {
    //$locationProvider.html5Mode(true);
});
module.controller("OplanSlotCtrl", function($scope, oplanHttp, $filter, $routeParams) {
    if (!$scope.slotId && $routeParams.id) {
        $scope.slotId = $routeParams.id;
    }
    if (!$scope.slotId) { $scope.error = "Bitte Parameter id angeben"; return; }
    var columnDefs = [
        {headerName: "Kommentar", field: "kommentar", editable: true, width: 240},
        {headerName: "Min. Plätze", field: "min_platz", editable: true, width: 90},
        {headerName: "Raumnr. Präferenz", field: "praeferenz", editable: true, width: 140},
        {headerName: "Raumnr. zugeteilt", field: "raum", cellRenderer: raumAuswahl, width: 140  }
    ];

    var frei = [];
    var rowData = [];

    $scope.gridOptions = {
        columnDefs: columnDefs,
        rowData: [],
        dontUseScrolls: true, // because so little data, no need to use scroll bars,
        angularCompileRows: true
    };
    
    $scope.$watch('slotId' , function() {
        oplanHttp.doGet("slot", { "id" : $scope.slotId }).success(function(result) {
            $scope.gridOptions.rowData = result.raumbedarf;
            $scope.slot = result.slot;
            $scope.slot.von = new Date($scope.slot.von);
            $scope.slot.bis = new Date($scope.slot.bis);
            frei = result.frei;
            $scope.gridOptions.api.onNewRows();
        });
    });
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
            let index=i;
            oplanHttp.belegeRaum(it[i], rows[i].id)
            .success(function() {
                rows[index].raum = it[index];
            })
            .error(function() {
                rows[index].raum ="error";
            });
        }
    }
    
    $scope.addRow = function() {
        var rows = $scope.gridOptions.rowData;
        var last = rows[rows.length - 1];
        var komm = last.kommentar;
        komm = komm.substr(0, komm.length-1) + String.fromCharCode(komm.charCodeAt(komm.length-1)+1);
        rows.push({
            kommentar: komm, min_platz: last.min_platz, praeferenz: "", raum: ""
        });
        $scope.gridOptions.api.onNewRows();
    }
    
    function raumDetails(raum) {
        var week = moment($scope.slot.von).isoWeek();
        window.open("#/raumplan/" + raum+"?w="+week,"","width=850,height=730");
    }

    function raumAuswahl(params) {
        var html = '<span ng-click="startEditing()" ng-right-click="details()">{{data.'+params.colDef.field+' || "nicht zugewiesen"}}</span> ';
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
            var elHeight = frei.length*35;
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
                    if (e.target.getAttribute("data-raumnr")) {
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
                                if (data && data.overlaps) {
                                  alert("Konnte Raum nicht zuweisen, da schon belegt: "+data.overlaps[0].kommentar+" - "+data.overlaps[0].von);
                                } else {
                                    alert("Allgemeiner Fehler");
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

        }
        return html;
    }

});