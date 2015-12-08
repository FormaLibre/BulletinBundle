var courseManager = angular.module('CourseManager', ['genericSearch', 'data-table']);
var translator = window.Translator;

courseManager.config(function(clarolineSearchProvider) {
	clarolineSearchProvider.setBaseRoute('formalibre_bulletin_get_matiereoptions');
	clarolineSearchProvider.setSearchRoute('formalibre_bulletin_search_sessions');
	clarolineSearchProvider.setFieldRoute('formalibre_bulletin_get_matiereoptions_fields');
	clarolineSearchProvider.disablePager();
});

courseManager.controller('CourseController', function(
	$scope,
	$log,
	$http,
	$cacheFactory,
	clarolineSearch
) {
	$scope.savedSearch = [];
	$scope.search = '[]';
	$http.defaults.cache = true;
	$scope.options = undefined;

	$scope.dataTableOptions = {
 		scrollbarV: false,
 		columnMode: 'force',
        headerHeight: 50,
        footerHeight: 50,
 		paging: {
 			externalPaging: true,
 			size: 10
 		},
 		columns: [
 			{
	 			name: translator.trans('name', {}, 'platform'),
	 			prop: "name"
 			},
 			{
 				name: translator.trans('total', {}, 'platform'),
 				prop:'total'
 			},
 			{
 				name: translator.trans('position', {}, 'platform'),
 				prop: 'position'
 			}
 		]
 	};

	var setOptions = function(data, offset, size) {
		var options = data.options;
		$scope.options = options;
		$scope.dataTableOptions.paging.count = data.total;
	}

	$scope.paging = function(offset, size) {
		clarolineSearch.find($scope.savedSearch, offset, size).then(function(d) {
			setOptions(d.data, offset, size);
		});
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