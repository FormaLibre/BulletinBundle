var courseManager = angular.module('CourseManager', ['genericSearch', 'data-table']);

var translator = window.Translator;

courseManager.config(function(clarolineSearchProvider) {
	clarolineSearchProvider.setBaseRoute('formalibre_bulletin_get_sessions');
	clarolineSearchProvider.setSearchRoute('formalibre_bulletin_search_sessions');
	clarolineSearchProvider.setFieldRoute('formalibre_bulletin_get_sessions_fields');
});

courseManager.controller('CourseController', function(
	$scope,
	$log,
	$http,
	$cacheFactory,
	clarolineSearch
) {
	$scope.dataTableOptions = {
 		scrollbarV: false,
 		columnMode: 'force',
        headerHeight: 0,
        footerHeight: 50,
 		paging: {
 			externalPaging: true,
 			size: 10
 		},
 		column: [
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

 	$scope.courses = [];

});

courseManager.directive('coursemanager', [
	function coursemanager() {
		return {
			restrict: 'E',
			template: '
				<div align="center" class="action-bar">
				    <div class="panel-body">
						<clarolinesearch on-search="onSearch(selected)"></clarolinesearch>
				    </div>   
				</div>
				<div>
				<dtable 
					options="dataTableOptions" 
					rows="courses" 
					class="material" 
					on-page="paging(offset, size)"
				>
				</dtable>
			',
			replace: true,
			controller: 'CourseController',
	    	controllerAs: 'cc'
		}
	}
]);