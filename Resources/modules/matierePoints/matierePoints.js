import angular from 'angular/index'

import 'angular-ui-router'
import 'angular-bootstrap'
import 'angular-ui-translation/angular-translation'
import Routing from './routing.js'
import MatierePointsCtrl from './Controller/MatierePointsCtrl'
import MatierePointsConfirmationModalCtrl from './Controller/MatierePointsConfirmationModalCtrl'

angular.module('MatierePointsModule', [
  'ui.router',
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'ui.translation'
])
.controller('MatierePointsCtrl', ['$http', '$uibModal', MatierePointsCtrl])
.controller('MatierePointsConfirmationModalCtrl', MatierePointsConfirmationModalCtrl)
.config(Routing)
