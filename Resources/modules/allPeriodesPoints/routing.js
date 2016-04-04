export default function($stateProvider, $urlRouterProvider) {
  $stateProvider
    .state ('users', {
      url: '/users',
      template: require('./Partial/users.html'),
      controller: 'UsersCtrl',
      controllerAs: 'uc'
    })
    .state ('points', {
      url: '/users/{userId}/group/{groupId}',
      template: require('./Partial/points.html'),
      controller: 'PointsCtrl',
      controllerAs: 'pc'
    })
  $urlRouterProvider.otherwise('/users')
}
