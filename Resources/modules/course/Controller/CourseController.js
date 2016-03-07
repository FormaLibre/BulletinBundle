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
		this.courseOptions = undefined;
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
		 			prop: "course_session.course.title"
	 			},
	 			 			{
		 			name: this.translate('code', {}, 'platform'),
		 			prop: "course_session.course.code"
	 			},
	 			{
		 			name: this.translate('name', {}, 'platform'),
		 			prop: "course_session.name"
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
	        			return '<input type="number" value="{{ $row.position }}" ng-model=$row.position class="form-control" ng-model-options="{ debounce: 1000 }" ng-change="cmc.editPosition($row)"></input>';
	        		}
	 			}
	 		]
	 	};

	 	this._onSearch = this._onSearch.bind(this);
	}

	setCourseOptions(data, offset, size) {
		var courseOptions = data.options

		for (var i = 0; i < offset * size; i++) {
			courseOptions.unshift({})
		}
		
		this.courseOptions = courseOptions;
		this.dataTableOptions.paging.count = data.total
	}

	paging(offset, size) {
		this.ClarolineSearchService.find('api_get_search_matiere_options', this.savedSearch, offset, size).then(d =>
			this.setCourseOptions(d.data, offset, size)
		);
	}

	editTotal($row) {
		this.CourseAPIService.editTotal($row.id, $row.total)
	}

	editPosition($row) {
		this.CourseAPIService.editPosition($row.id, $row.position)
	}

    translate(key, data = {}) {
        return window.Translator.trans(key, data, 'platform');
    }

	_onSearch(searches) {
		this.dataTableOptions.paging.offset = 0;
		this.savedSearch = searches;
		//to channge
		this.ClarolineSearchService.find('api_get_search_matiere_options', searches, 0, this.dataTableOptions.paging.size).then(d => 
			this.setCourseOptions(d.data, 0, this.dataTableOptions.paging.size)
		);
	};
}