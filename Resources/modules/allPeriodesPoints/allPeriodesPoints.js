import 'angular/angular.min'

import UIRouter from 'angular-ui-router'
import dataTable from 'angular-data-table/release/dataTable.helpers.min'
import translation from 'angular-ui-translation/angular-translation'

import ClarolineSearch from '../../../../../claroline/core-bundle/Resources/modules/search/module'
import Routing from './routing.js'
import UsersCtrl from './Controller/UsersCtrl'
import PointsCtrl from './Controller/PointsCtrl'

angular.module('AllPeriodesPointsModule', [
  'ui.router',
  'data-table',
  'ClarolineSearch'
])
.controller('UsersCtrl', ['$http', 'ClarolineSearchService', UsersCtrl])
.controller('PointsCtrl', ['$stateParams', '$http', PointsCtrl])
.config(Routing)