import 'angular/angular.min'

import dataTable from 'angular-data-table/release/dataTable.helpers.min'
import translation from 'angular-ui-translation/angular-translation'
import SessionController from './Controller/SessionController'

angular.module('SessionManager', [
    'ClarolineSearch',
    'data-table',
    'ClarolineAPI',
]) .controller('UserController', ['$http', 'ClarolineSearchService', 'ClarolineAPIService', SessionController])