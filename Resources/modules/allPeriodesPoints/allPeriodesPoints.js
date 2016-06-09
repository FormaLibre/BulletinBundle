import 'angular/angular.min'

import UIRouter from 'angular-ui-router'
import dataTable from 'angular-data-table/release/dataTable.helpers.min'
import translation from 'angular-ui-translation/angular-translation'
import '../../../../../claroline/distribution/main/core/Resources/modules/fos-js-router/module'

import ClarolineSearch from '#/main/core/Resources/modules/search/module'
import Interceptors from '#/main/core/Resources/modules/interceptorsDefault'
import Routing from './routing.js'
import UsersCtrl from './Controller/UsersCtrl'
import PointsCtrl from './Controller/PointsCtrl'

angular.module('AllPeriodesPointsModule', [
  'ui.router',
  'ui.translation',
  'data-table',
  'ui.fos-js-router',
  'ClarolineSearch'
])
.config(Interceptors)
.controller('UsersCtrl', ['$http', 'ClarolineSearchService', UsersCtrl])
.controller('PointsCtrl', ['$stateParams', '$http', PointsCtrl])
.config(Routing)
