export default class SessionController {
    constructor($http, ClarolineSearchService, ClarolineAPIService, $cacheFactory) {
        this.$http = $http
        this.$cacheFactory = $cacheFactory
        this.ClarolineSearchService = ClarolineSearchService
        this.ClarolineAPIService = ClarolineAPIService
        this.addedSessions = []
        this.refreshSelected = true
        this.search = ''
        this.savedSearch = []
        this.sessions = undefined
        this.selectedRows = []
        this.saveSelected = []
        this.fields = []

        $http.get(Routing.generate('api_get_session_searchable_fields')).then(d => this.fields = d.data)

        this.columns = [
            {name: this.translate('title'), prop: "course.title", isCheckboxColumn: true, headerCheckbox: true},
            {name: this.translate('name'), prop: "name"},
            {name: this.translate('code'), prop: "course.code"}
        ];

        this.dataTableOptions = {
            scrollbarV: false,
            columnMode: 'force',
            headerHeight: 50,
            footerHeight: 50,
            selectable: true,
            multiSelect: true,
            checkboxSelection: true,
            columns: this.columns,
            loadingMessage: this.translate('loading') + '...',
            paging: {
                externalPaging: true,
                size: 10
            }
        };

        this._onSearch = this._onSearch.bind(this)
    }

    translate(key, data = {}) {
        return window.Translator.trans(key, data, 'platform');
    }

    paging(offset, size) {
        //this is dirty but the app isn't full angular yet. Once we'll be able to pick w/e periode we need, this should be routed
        this.ClarolineSearchService.find('api_get_search_periode_admin_session', this.savedSearch, offset, size, {'periode': AngularApp.periode}).then(d => {
            this.setSessions(d.data, offset, size)
        })
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
        for (var i = 0; i < this.savedSearch.length; i++) {
            qs += this.savedSearch[i].field +'[]=' + this.savedSearch[i].value + '&';
        } 

        route += qs;
        console.log(route);

        this.$http.post(route);
    }

    setSessions(data, offset, size) {
        var sessions = data.sessions;
        this.dataTableOptions.paging.count = data.total;

        //I know it's terrible... but I have no other choice with this table.
            for (var i = 0; i < offset * size; i++) {
                sessions.unshift({});
            }
            
        this.sessions = sessions;
        this.setSelected(sessions);
    }

    setSelected(sessions) {
        if (this.refreshSelected) {
            this.selectedRows.splice(0, this.selectedRows.length);

            for (var i = 0; i < sessions.length; i++) {
                if (sessions[i].extra && sessions[i].extra.linked === true) {
                    this.selectedRows.push(sessions[i]);
                }
            }

            this.refreshSelected = false;
        } 
    }

    _onSearch(searches) {
        this.dataTableOptions.paging.offset = 0;
        this.savedSearch = searches;
        this.refreshSelected = true;

        //this is dirty but the app isn't full angular yet. Once we'll be able to pick w/e periode we need, this should be routed
        this.ClarolineSearchService.find('api_get_search_periode_admin_session', this.savedSearch, 0, this.dataTableOptions.paging.size, {'periode': AngularApp.periode}).then(d => {
            this.setSessions(d.data, 0, this.dataTableOptions.paging.size)
            this.dataTableOptions.paging.count = d.data.total
        })
    };
}