export default class CourseController {
	constructor($http, ClarolineSearchService, ClarolineAPIService, CourseAPIService, $cacheFactory) {
		this.$http = $http
		this.ClarolineSearchService = ClarolineSearchService
		this.ClarolineAPIService = ClarolineAPIService
		this.CourseAPIService = CourseAPIService
		this.$cacheFactory = $cacheFactory
		this.savedSearch = []
		this.search = '[]'
		//$http.defaults.cache = true;
		this.sessions = undefined;
		this.fields = []

		$http.get(Routing.generate('api_get_matiere_options_searchable_fields')).then(d => this.fields = d.data)

		this.dataTableOptions = {
	 		scrollbarV: false,
	 		columnMode: 'force',
      headerHeight: 50,
      footerHeight: 50,
      loadingMessage: this.translate('loading', {}, 'platform') + '...',
	 		paging: {
	 			externalPaging: true,
	 			size: 20
	 		},
	 		columns: [
	 			{
		 			name: this.translate('title', {}, 'platform'),
		 			prop: "course.title"
	 			},
	 			 			{
		 			name: this.translate('code', {}, 'platform'),
		 			prop: "course.code"
	 			},
	 			{
		 			name: this.translate('name', {}, 'platform'),
		 			prop: "name"
	 			},
	 			{
	 				name: this.translate('certificated', {}, 'platform'),
	 				prop:'certificated',
          cellRenderer: function() {
            return '<input type="checkbox" value="{{ $row.certificated }}" ng-model=$row.certificated ng-model-options="{ debounce: 1000 }" ng-change="cmc.editCertificated($row)"></input>';
          }
	 			},
	 			{
	 				name: this.translate('total', {}, 'platform'),
	 				prop:'total',
          cellRenderer: function() {
            return '<input type="number" value="{{ $row.total }}" ng-model=$row.total class="form-control" ng-model-options="{ debounce: 1000 }" ng-change="cmc.editTotal($row)"></input>';
          }
	 			},
	 			{
	 				name: this.translate('position', {}, 'platform'),
	 				prop: 'position',
          cellRenderer: function() {
            return '<input type="number" value="{{ $row.displayOrder }}" ng-model=$row.displayOrder class="form-control" ng-model-options="{ debounce: 1000 }" ng-change="cmc.editPosition($row)"></input>';
          }
	 			},
	 			{
	 				name: this.translate('color', {}, 'platform'),
	 				prop: 'color',
          cellRenderer: function() {
            return '<input colorpicker="hex" type="text" value="{{ $row.color }}" ng-model=$row.color class="form-control" ng-model-options="{ debounce: 1000 }" ng-change="cmc.editColor($row)"></input>';
          }
	 			}
	 		]
	 	};

	 	this._onSearch = this._onSearch.bind(this);
	}

	setSessions(data, offset, size) {
		var sessions = data.sessions
    sessions.forEach(s => {
      s['color'] = s['details']['color'] ? s['details']['color'] : null
      s['total'] = s['details']['total'] ? parseInt(s['details']['total']) : null
      s['certificated'] = s['details']['certificated'] !== undefined ? s['details']['certificated'] : true
      s['displayOrder'] = parseInt(s['displayOrder'])
    })

		for (var i = 0; i < offset * size; i++) {
      sessions.unshift({})
		}
		
		this.sessions = sessions;
		this.dataTableOptions.paging.count = data.total
	}

	paging(offset, size) {
		this.ClarolineSearchService.find('api_get_search_sessions', this.savedSearch, offset, size).then(d =>
			this.setSessions(d.data, offset, size)
		);
	}

	editTotal($row) {
		this.CourseAPIService.editTotal($row.id, $row.total)
	}

	editCertificated($row) {
    const certificated = $row.certificated ? 1 : 0
		this.CourseAPIService.editCertificated($row.id, certificated)
	}

	editPosition($row) {
		this.CourseAPIService.editPosition($row.id, $row.displayOrder)
	}

	editColor($row) {
		this.CourseAPIService.editColor($row.id, $row.color)
	}

    translate(key, data = {}, domain = 'platform') {
        return window.Translator.trans(key, data, domain);
    }

	_onSearch(searches) {
		this.dataTableOptions.paging.offset = 0;
		this.savedSearch = searches;
		//to channge
		this.ClarolineSearchService.find('api_get_search_sessions', searches, 0, this.dataTableOptions.paging.size).then(d =>
			this.setSessions(d.data, 0, this.dataTableOptions.paging.size)
		);
	};
}