import angular from 'angular/index'

import 'angular-ui-router'
import 'angular-bootstrap'
import 'angular-ui-translation/angular-translation'
import '#/main/core/services/module'
import Routing from './routing.js'
import ElevePointsCtrl from './Controller/ElevePointsCtrl'
import ElevePointsConfirmationModalCtrl from './Controller/ElevePointsConfirmationModalCtrl'

angular.module('ElevePointsModule', [
  'ui.router',
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'ui.translation',
  'ClarolineAPI'
])
.controller('ElevePointsCtrl', ['$http', '$uibModal', 'ClarolineAPIService', ElevePointsCtrl])
.controller('ElevePointsConfirmationModalCtrl', ElevePointsConfirmationModalCtrl)
.config(Routing)
