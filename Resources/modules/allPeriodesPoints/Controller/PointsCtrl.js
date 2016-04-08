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
    this.codes = {}
    this.initialize()
  }

  initialize () {
    const route = Routing.generate('api_get_all_users_points', {user: this.userId})
    this.$http.get(route).then(d => {
      this.user = d['data']['user']
      this.pointsDatas = d['data']['matieres']
      this.periodes = d['data']['periodes']
      this.matieresPeriodes = d['data']['matieresPeriodes']

      for (let pempId in d['data']['pemps']) {
        this.pemps[pempId] = d['data']['pemps'][pempId]
      }

      for (let pepdpId in d['data']['pepdps']) {
        this.pepdps[pepdpId] = d['data']['pepdps'][pepdpId]
      }

      for (let code in d['data']['codes']) {
        this.codes[code] = d['data']['codes'][code]
      }
      console.log(`Nb points : ${d['data']['nbUserPoints']}`)
      console.log(`Nb points divers : ${d['data']['nbUserPointsDivers']}`)
      console.log(`Nb created points : ${d['data']['nbCreatedUserPoints']}`)
      console.log(`Nb created points divers : ${d['data']['nbCreatedUserPointsDivers']}`)
    })
  }

  validate () {
    const route = Routing.generate(
      'api_put_all_users_points',
      {user: this.userId,  points: this.pemps, pointsDivers: this.pepdps}
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

  computeMatiereTotal (matiereId) {
    let points = 0
    let total = 0
    let percentage = 0

    for (let periodeId in this.pointsDatas[matiereId]['periodes']) {
      if (this.pointsDatas[matiereId]['periodes'][periodeId]['pempId'] && this.pointsDatas[matiereId]['periodes'][periodeId]['total']) {
        const pempId = this.pointsDatas[matiereId]['periodes'][periodeId]['pempId']
        const isCode = this.codes[parseFloat(this.pemps[pempId])] ? true : false
        const ignored = isCode ? this.codes[parseFloat(this.pemps[pempId])]['ignored'] : false

        if (!ignored) {
          total += parseInt(this.pointsDatas[matiereId]['periodes'][periodeId]['total'])
          points += this.pemps[pempId] && !isCode ? parseFloat(this.pemps[pempId]) : 0
        }
      }
    }

    if (total > 0) {
      const ratio = total / 100
      percentage = points / ratio
    }

    return `${percentage} %`
  }
}