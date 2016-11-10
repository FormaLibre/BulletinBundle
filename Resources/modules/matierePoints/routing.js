export default function($stateProvider, $urlRouterProvider) {
  $stateProvider
    .state ('points', {
      url: '/points',
      template: require('./Partial/matiere_points.html'),
      controller: 'MatierePointsCtrl',
      controllerAs: 'mpc'
    })
  $urlRouterProvider.otherwise('/points')
}
