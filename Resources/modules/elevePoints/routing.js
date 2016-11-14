export default function($stateProvider, $urlRouterProvider) {
  $stateProvider
    .state ('points', {
      url: '/points',
      template: require('./Partial/eleve_points.html'),
      controller: 'ElevePointsCtrl',
      controllerAs: 'epc'
    })
  $urlRouterProvider.otherwise('/points')
}
