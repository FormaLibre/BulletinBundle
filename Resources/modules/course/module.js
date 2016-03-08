import 'angular/angular.min'

import dataTable from 'angular-data-table/release/dataTable.helpers.min'
import translation from 'angular-ui-translation/angular-translation'

import CourseManagerDirective from './Directive/CourseManagerDirective'
import CourseAPIService from './Service/CourseAPIService'

//claroline
import ClarolineSearch from '../../../../../claroline/core-bundle/Resources/modules/search/module'
import ClarolineAPI from '../../../../../claroline/core-bundle/Resources/modules/services/module'

angular.module('CourseManager', [
    'ClarolineSearch',
    'data-table',
    'ClarolineAPI',
]) 
    .directive('coursemanager', () => new CourseManagerDirective)
    .service('CourseAPIService', CourseAPIService)