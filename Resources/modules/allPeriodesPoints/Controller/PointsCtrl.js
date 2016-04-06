export default class PointsCtrl {
  constructor ($stateParams, $http) {
    this.$http = $http
    this.userId = $stateParams.userId
    this.user = {}
    this.pointsDatas = {}
    this.periodes = []
    this.matieresPeriodes = {}
    this.pemps = {}
    this.pepdps = {}
    this.initialize()
  }

  initialize () {
    const route = Routing.generate('api_get_all_users_points', {user: this.userId})
    this.$http.get(route).then(d => {
      this.user = d['data']['user']
      this.pointsDatas = d['data']['matieres']
      this.periodes = d['data']['periodes']
      this.matieresPeriodes = d['data']['matieresPeriodes']
      console.log(`Nb points : ${d['data']['nbUserPoints']}`)
      console.log(`Nb points divers : ${d['data']['nbUserPointsDivers']}`)

      for (const matiereId in this.pointsDatas) {
        const periodes = this.pointsDatas[matiereId]['periodes']

        for (const periodeId in periodes) {
          this.pemps[periodes[periodeId]['pempId']] = periodes[periodeId]['point']
        }
      }

      for (const periodeId in this.periodes) {
        const pointsDivers = this.periodes[periodeId]['pointsDivers']

        for (const pointDiversId in pointsDivers) {
          this.pepdps[pointsDivers[pointDiversId]['pepdpId']] = pointsDivers[pointDiversId]['point']
        }
      }
    })
  }

  validate () {
    const pointsJson = JSON.stringify(this.pemps)
    const pointsDiversJson = JSON.stringify(this.pepdps)
    const route = Routing.generate(
      'api_put_all_users_points',
      {user: this.userId,  points: pointsJson, pointsDivers: pointsDiversJson}
    )
    this.$http.put(route).then(d => {
      //console.log(d)
      const pointsDatas = d['data']['points']
      const pointsDiversDatas = d['data']['pointsDivers']

      for (const pempId in pointsDatas) {
        this.pemps[pempId] = pointsDatas[pempId]
      }

      for (const pepdpId in pointsDiversDatas) {
        this.pepdps[pepdpId] = pointsDiversDatas[pepdpId]
      }
    })
  }
}