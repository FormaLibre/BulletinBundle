import 'angular/angular.min'
import 'angular-bootstrap'
import 'angular-animate'
import 'angular-ui-router'
import 'angular-loading-bar'
import 'angular-ui-translation/angular-translation'

import '#/main/core/services/module'
import Routing from './routing.js'
import GroupService from './Service/GroupService'
import GroupsManagementCtrl from './Controller/GroupsManagementCtrl'
import GroupsSelectionModalCtrl from './Controller/GroupsSelectionModalCtrl'

angular.module('GroupsModule', [
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'ngAnimate',
  'ui.router',
  'ui.translation',
  'angular-loading-bar',
  'ngTable',
  'ClarolineAPI'
])
.service('GroupService', GroupService)
.controller('GroupsManagementCtrl', ['NgTableParams', 'GroupService', GroupsManagementCtrl])
.controller('GroupsSelectionModalCtrl', GroupsSelectionModalCtrl)
.config(Routing)
.config([
  'cfpLoadingBarProvider',
  function configureLoadingBar (cfpLoadingBarProvider) {
    // Configure loader
    cfpLoadingBarProvider.latencyThreshold = 200
    cfpLoadingBarProvider.includeBar = true
    cfpLoadingBarProvider.includeSpinner = true
  }
])
