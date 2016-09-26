import 'angular/angular.min'

import dataTable from 'angular-data-table/release/dataTable.helpers.min'
import translation from 'angular-ui-translation/angular-translation'
import SessionManagerDirective from './Directive/SessionManagerDirective'

//claroline
import ClarolineSearch from '#/main/core/search/module'
import ClarolineAPI from '#/main/core/services/module'

angular.module('SessionManager', [
    'ClarolineSearch',
    'data-table',
    'ClarolineAPI',
]) 
    .directive('sessionManager', () => new SessionManagerDirective)
