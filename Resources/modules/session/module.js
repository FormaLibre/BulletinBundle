import 'angular/angular.min'

import dataTable from 'angular-data-table/release/dataTable.helpers.min'
import translation from 'angular-ui-translation/angular-translation'
import SessionManagerDirective from './Directive/SessionManagerDirective'

//claroline
import ClarolineSearch from '../../../../../../../claroline/core-bundle/Resources/modules/search/module'
import ClarolineAPI from '../../../../../../../claroline/core-bundle/Resources/modules/services/module'

angular.module('SessionManager', [
    'ClarolineSearch',
    'data-table',
    'ClarolineAPI',
]) 
    .directive('sessionManager', () => new SessionManagerDirective)