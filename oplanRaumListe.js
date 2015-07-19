
var module = angular.module("oplanRaumListe", ["angularGrid"]);
module.config(function($locationProvider) {
    //$locationProvider.html5Mode(true);
});
module.controller("OplanRaumListeCtrl", function($scope, $http, $location, $filter) {
    var columnDefs = [
        {headerName: "Raum-Nummer", field: "raum_nummer", editable: true, width:120},
        {headerName: "Tag", field: "von_day", editable: true, width: 80},
        {headerName: "von", field: "von_time", editable: true, width: 80},
        {headerName: "bis", field: "bis_time", editable: true, width: 80},
        {headerName: "Status", field: "status", editable: true},
        {headerName: "Kommentar", field: "kommentar", editable: true},
        {headerName: "Belegt", field: "belegt", editable: false, cellClicked: onClick}
    ];

    var frei = [];
    var rowData = [];

    $scope.gridOptions = {
        columnDefs: columnDefs,
        rowData: [],
        angularCompileRows: true,
        enableSorting: true,
        showToolPanel: true,
        groupKeys: ['von_day'],
        groupDefaultExpanded: true,
        groupUseEntireRow: true,
        enableColResize: true
    };

    $http.get("raum.php").success(function(result) {
        $scope.gridOptions.rowData = result.frei;
        $scope.gridOptions.api.onNewRows();
        $scope.gridOptions.api.setSortModel([
          {field: 'raum_nummer', sort: 'asc'},
          {field: 'von_time', sort: 'asc'}
        ]);
    });

    function onClick(e) {
        $scope.$apply(function() {
            console.log(e);
            var week = moment(e.data.von).isoWeek();
            $location.path("/raumplan/" + e.data.raum_nummer).search("w", week);
        });
    }
});
module.controller("OplanTucanRaumListeCtrl", function($scope, $http) {
    
    $http.get("raum.php?order=tucan").success(function(result) {
        var data = {};
        result.frei.forEach(function(row) {
            var key = row.von_day + " " + row.von_time + " " + row.bis_time;
            if (!data[key]) data[key] = [];
            row.von = new Date(row.von);
            data[key].push(row);
        });
        $scope.data = data;
    });
});


