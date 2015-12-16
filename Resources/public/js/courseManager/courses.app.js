var courseManager = angular.module('CourseManager', ['genericSearch', 'data-table']);
var translator = window.Translator;

courseManager.config(function(clarolineSearchProvider) {
	clarolineSearchProvider.setBaseRoute('formalibre_bulletin_get_matiereoptions');
	clarolineSearchProvider.setSearchRoute('formalibre_bulletin_search_matiereoptions');
	clarolineSearchProvider.setFieldRoute('formalibre_bulletin_get_matiereoptions_fields');
});

courseManager.controller('CourseController', function(
	$scope,
	$log,
	$http,
	$cacheFactory,
	clarolineSearch,
	API
) {
	$scope.savedSearch = [];
	$scope.search = '[]';
	//$http.defaults.cache = true;
	$scope.courseOptions = undefined;

	$scope.dataTableOptions = {
 		scrollbarV: false,
 		columnMode: 'force',
        headerHeight: 50,
        footerHeight: 50,
        loadingMessage: translator.trans('loading', {}, 'platform') + '...',
 		paging: {
 			externalPaging: true,
 			size: 20
 		},
 		columns: [
 			{
	 			name: translator.trans('title', {}, 'platform'),
	 			prop: "course_session.course.title"
 			},
 			 			{
	 			name: translator.trans('code', {}, 'platform'),
	 			prop: "course_session.course.code"
 			},
 			{
	 			name: translator.trans('name', {}, 'platform'),
	 			prop: "course_session.name"
 			},
 			{
 				name: translator.trans('total', {}, 'platform'),
 				prop:'total',
		        cellRenderer: function() {
        			return '<input type="number" value="{{ $row.total }}" ng-model=$row.total class="form-control" ng-model-options="{ debounce: 1000 }" ng-change="editTotal($row)"></input>';
        		}
 			},
 			{
 				name: translator.trans('position', {}, 'platform'),
 				prop: 'position',
		        cellRenderer: function() {
        			return '<input type="number" value="{{ $row.position }}" ng-model=$row.position class="form-control" ng-model-options="{ debounce: 1000 }" ng-change="editPosition($row)"></input>';
        		}
 			}
 		]
 	};

	var setCourseOptions = function(data, offset, size) {
		var courseOptions = data.options;

		for (var i = 0; i < offset * size; i++) {
			courseOptions.unshift({});
		}
		
		$scope.courseOptions = courseOptions;
		$scope.dataTableOptions.paging.count = data.total;
	}

	$scope.onSearch = function(searches) {
		$scope.dataTableOptions.paging.offset = 0;
		$scope.savedSearch = searches;
		clarolineSearch.find(searches, 0, $scope.dataTableOptions.paging.size).then(function(d) {
			setCourseOptions(d.data, 0, $scope.dataTableOptions.paging.size);
		});
	};

	$scope.paging = function(offset, size) {
		clarolineSearch.find($scope.savedSearch, offset, size).then(function(d) {
			setCourseOptions(d.data, offset, size);
		});
	}

	$scope.editTotal = function($row) {
		API.editTotal($row.id, $row.total);
	}

	$scope.editPosition = function($row) {
		API.editPosition($row.id, $row.position);
	}
});

courseManager.directive('coursemanager', [
	function coursemanager() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/formalibrebulletin/js/courseManager/view.html',
			replace: true,
			controller: 'CourseController'
		}
	}
]);

courseManager.factory('API', function($http) {
	return {
		editPosition: function(id, position) {
			var route = Routing.generate('formalibre_bulletin_set_option_position', {'matiereOptions': id, 'position': position});
			$http.post(route);
		},
		editTotal: function(id, total) {
			var route = Routing.generate('formalibre_bulletin_set_option_total', {'matiereOptions': id, 'total': total});
			$http.post(route);
		}
	}
});