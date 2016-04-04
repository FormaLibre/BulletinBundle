export default class PointsCtrl {
  constructor ($stateParams, $http) {
    this.$http = $http
    this.userId = $stateParams.userId
    this.groupId  = $stateParams.groupId
    this.initialize()
  }

  initialize () {
    console.log('initialize')
  }
}