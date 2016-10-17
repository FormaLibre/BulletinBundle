/*global Translator*/

import groupsManagementTemplate from './Partial/groups_management.html'

export default function($stateProvider, $urlRouterProvider) {
  $stateProvider
    .state ('groups_management', {
      url: '/groups',
      template: groupsManagementTemplate,
      controller: 'GroupsManagementCtrl',
      controllerAs: 'gmc'
    })

  $urlRouterProvider.otherwise('/groups')
}
