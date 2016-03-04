export default class SessionController {
    constructor($http, $cacheFactory, ClarolineSearchService, ClarolineAPIService) {
        this.$http = $http
        this.$cacheFactory = $cacheFactory
        this.ClarolineSearchService = ClarolineSearchService
        this.ClarolineAPIService = ClarolineAPIService
        this.addedSessions = [];
        this.refreshSelected = true;
        this.search = '';
        this.savedSearch = [];
        this.sessions = undefined;
        this.selectedRows = [];
        this.saveSelected = [];

        this.columns = [
            {name: translate('title'), prop: "course.title", isCheckboxColumn: true, headerCheckbox: true},
            {name: translate('name'), prop: "name"},
            {name: translate('code'), prop: "course.code"}
        ];

        this.dataTableOptions = {
            scrollbarV: false,
            columnMode: 'force',
            headerHeight: 50,
            footerHeight: 50,
            selectable: true,
            multiSelect: true,
            checkboxSelection: true,
            columns: $scope.columns,
            loadingMessage: this.translate.trans('loading') + '...',
            paging: {
                externalPaging: true,
                size: 10
            }
        };
    }

    translate(key, data = {}) {
        return window.Translator.trans(key, data, 'platform');
    }

    onSearch(searches) {
        this.dataTableOptions.paging.offset = 0;
        this.savedSearch = searches;
        refreshSelected = true;
        clarolineSearch.find(searches, 0, this.dataTableOptions.paging.size).then(function(d) {
            setSessions(d.data, 0, this.dataTableOptions.paging.size);
        });
    };

    paging(offset, size) {
        clarolineSearch.find(this.savedSearch, offset, size).then(function(d) {
            setSessions(d.data, offset, size);
        });
    }

    onCheck(rows) {
        const row = rows[0];
        this.$http.post(
            Routing.generate('formalibre_bulletin_add_session_to_periode', {'periode': AngularApp.periode, 'session': row.id})
        );
    }

    onUncheck(rows) {
        const row = rows[0];
        this.post(
            Routing.generate('formalibre_bulletin_remove_session_from_periode', {'periode': AngularApp.periode, 'session': row.id}),
            [row]
        );
    }

    onHeaderCheckboxChange(isChecked) {
        var route = (isChecked) ?
            Routing.generate('formalibre_bulletin_add_search_sessions', {'periode': AngularApp.periode}):
            Routing.generate('formalibre_bulletin_remove_search_sessions', {'periode': AngularApp.periode});

        var qs = '?';
        for (var i = 0; i < $scope.savedSearch.length; i++) {
            qs += $scope.savedSearch[i].field +'[]=' + $scope.savedSearch[i].value + '&';
        } 

        route += qs;
        console.log(route);

        $http.post(route);
    }

    setSessions(data, offset, size) {
        var sessions = data.sessions;
        $scope.dataTableOptions.paging.count = data.total;

        //I know it's terrible... but I have no other choice with this table.
            for (var i = 0; i < offset * size; i++) {
                sessions.unshift({});
            }
            
        $scope.sessions = sessions;
        setSelected(sessions);
    }

    setSelected(sessions) {
        if (refreshSelected) {
            $scope.selectedRows.splice(0, $scope.selectedRows.length);

            for (var i = 0; i < sessions.length; i++) {
                if (sessions[i].extra && sessions[i].extra.linked === true) {
                    $scope.selectedRows.push(sessions[i]);
                }
            }

            //refreshSelected = false;
        } 
    }
}