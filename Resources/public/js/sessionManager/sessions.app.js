var sessionManager = angular.module('sessionsManager', ['genericSearch', 'data-table']);
var translator = window.Translator;

sessionManager.config(function(clarolineSearchProvider) {
	clarolineSearchProvider.setBaseRoute('formalibre_bulletin_get_sessions', {'periode': AngularApp.periode});
	clarolineSearchProvider.setSearchRoute('formalibre_bulletin_search_sessions', {'periode': AngularApp.periode});
	clarolineSearchProvider.setFieldRoute('formalibre_bulletin_get_sessions_fields');
	clarolineSearchProvider.disablePager();
});

sessionManager.controller('sessionsCtrl', function(
	$scope,
	$log,
	$http,
	$cacheFactory,
	clarolineSearch
) {
	$http.defaults.cache = true;
	$scope.addedSessions = [];
	var refreshSelected = true;

	var translate = function(key) {
		return translator.trans(key, {}, 'platform');
	}

	var setSessions = function(data, offset, size) {
		var sessions = data.sessions;
		$scope.dataTableOptions.paging.count = data.total;
		$scope.sessions = sessions;
		setSelected(sessions);
	}

	var setSelected = function(sessions) {
		if (refreshSelected) {
			$scope.selectedRows.splice(0, $scope.selectedRows.length);

			for (var i = 0; i < sessions.length; i++) {
				if (sessions[i].extra.linked === true) {
					$scope.selectedRows.push(sessions[i]);
				}
			}

			refreshSelected = false;
		} 
	}

	$scope.search = '';
	$scope.savedSearch = [];
	$scope.sessions = undefined;
	$scope.selectedRows = [];
	$scope.saveSelected = [];

	$scope.columns = [
		{name: translate('title'), prop: "course.title", isCheckboxColumn: true, headerCheckbox: true},
		{name: translate('name'), prop: "name"},
		{name: translate('code'), prop: "course.code"}
	];

	$scope.dataTableOptions = {
		scrollbarV: false,
 		columnMode: 'force',
        headerHeight: 50,
        footerHeight: 50,
        selectable: true,
        multiSelect: true,
        checkboxSelection: true,
 		columns: $scope.columns,
 		loadingMessage: translator.trans('loading', {}, 'platform') + '...',
 		paging: {
 			externalPaging: true,
 			size: 10
 		}
	};
	
	$scope.clarolineSearch = function(searches) {
		$scope.dataTableOptions.paging.offset = 0;
		$scope.savedSearch = searches;
		refreshSelected = true;
		clarolineSearch.find(searches, 0, $scope.dataTableOptions.paging.size).then(function(d) {
			setSessions(d.data, 0, $scope.dataTableOptions.paging.size);
		});
	};

	$scope.paging = function(offset, size) {
		clarolineSearch.find($scope.savedSearch, offset, size).then(function(d) {
			setSessions(d.data, offset, size);
		});
	}

	$scope.onSelect = function(rows) {
		//
	}
});

sessionManager.directive('sessionslist', [
	function sessionslist() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/formalibrebulletin/js/sessionManager/views/sessionslist.html',
			replace: true
		}
	}
]);

sessionManager.directive('sessionssearch', [
	function sessionsearch() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/formalibrebulletin/js/sessionManager/views/sessionssearch.html',
			replace: true
		}
	}
]);