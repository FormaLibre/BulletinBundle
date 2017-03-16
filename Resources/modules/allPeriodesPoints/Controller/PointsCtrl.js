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
    this.totalMatieres = {}
    this.totalPeriodes = {}
    this.totalPointsDivers = {}
    this.finalPercentage = 0
    this.pointsDiversDatas = []
    this.eleveMatiereOptions = {}
    this.initialize()
  }

  initialize () {
    const route = Routing.generate('api_get_all_users_points', {user: this.userId})
    this.$http.get(route).then(d => {
      this.user = d['data']['user']
      this.pointsDatas = d['data']['matieres']
      this.periodes = d['data']['periodes']
      this.matieresPeriodes = d['data']['matieresPeriodes']
      this.totalMatieres = d['data']['totalMatieres']
      this.totalPeriodes = d['data']['totalPeriodes']
      this.totalPointsDivers = d['data']['totalPointsDivers']
      this.finalPercentage = d['data']['finalPercentage']

      for (let pempId in d['data']['pemps']) {
        this.pemps[pempId] = d['data']['pemps'][pempId]
      }

      for (let pepdpId in d['data']['pepdps']) {
        this.pepdps[pepdpId] = d['data']['pepdps'][pepdpId]
      }

      for (let code in d['data']['codes']) {
        this.codes[code] = d['data']['codes'][code]
      }

      for (let pointDiversId in d['data']['pointsDiversDatas']) {
        this.pointsDiversDatas.push(d['data']['pointsDiversDatas'][pointDiversId])
      }

      for (let matiereId in this.pointsDatas) {
        this.eleveMatiereOptions[matiereId] = {
          id: this.pointsDatas[matiereId]['optionsId'],
          deliberated: this.pointsDatas[matiereId]['deliberated']
        }
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
      {user: this.userId,  points: this.pemps, pointsDivers: this.pepdps, eleveMatiereOptions: this.eleveMatiereOptions}
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

  computeMatiereTotal (matiereId, inPercentage = true) {
    let points = 0
    let total = 0
    let percentage = 0
    let result = ''

    for (let periodeId in this.pointsDatas[matiereId]['periodes']) {
      if (this.pointsDatas[matiereId]['periodes'][periodeId] &&
        this.pointsDatas[matiereId]['periodes'][periodeId]['pempId'] &&
        this.pointsDatas[matiereId]['periodes'][periodeId]['total']) {

        const pempId = this.pointsDatas[matiereId]['periodes'][periodeId]['pempId']
        const isCode = this.codes[parseFloat(this.pemps[pempId])] ? true : false
        const ignored = isCode ? this.codes[parseFloat(this.pemps[pempId])]['ignored'] : false

        if (!ignored) {
          total += parseInt(this.pointsDatas[matiereId]['periodes'][periodeId]['total'])
          points += this.pemps[pempId] && !isCode ? parseFloat(this.pemps[pempId]) : 0
        }
      }
    }

    if (inPercentage) {
      if (total > 0) {
        const ratio = total / 100
        percentage = Math.round((points / ratio) * 10) / 10
      }
      result = percentage
    } else {
      result = `${Math.round(points * 10) / 10} / ${total}`
    }

    return result
  }

  computePeriodeTotal (periodeId, inPercentage = true) {
    let points = 0
    let total = 0
    let percentage = 0
    let result = ''

    for (let matiereId in this.pointsDatas) {
      if (this.pointsDatas[matiereId]['periodes'][periodeId] &&
        this.pointsDatas[matiereId]['periodes'][periodeId]['pempId'] &&
        this.pointsDatas[matiereId]['periodes'][periodeId]['total'] &&
        this.pointsDatas[matiereId]['periodes'][periodeId]['certificated']) {

        const pempId = this.pointsDatas[matiereId]['periodes'][periodeId]['pempId']
        const isCode = this.pemps[pempId] && this.codes[parseFloat(this.pemps[pempId])] ? true : false
        const ignored = isCode ? this.codes[parseFloat(this.pemps[pempId])]['ignored'] : false

        if (!ignored) {
          total += parseInt(this.pointsDatas[matiereId]['periodes'][periodeId]['total'])
          points += this.pemps[pempId] && !isCode ? parseFloat(this.pemps[pempId]) : 0
        }
      }
    }

    if (inPercentage) {
      if (total > 0) {
        const ratio = total / 100
        percentage = Math.round((points / ratio) * 10) / 10
      }
      result = percentage
    } else {
      result = `${Math.round(points * 10) / 10} / ${total}`
    }

    return result
  }

  computeFinalTotal (inPercentage = true) {
    let points = 0
    let total = 0
    let percentage = 0
    let result = ''

    for (let matiereId in this.pointsDatas) {
      for (let periodeId in this.pointsDatas[matiereId]['periodes']) {
        if (this.pointsDatas[matiereId]['periodes'][periodeId] &&
          this.pointsDatas[matiereId]['periodes'][periodeId]['pempId'] &&
          this.pointsDatas[matiereId]['periodes'][periodeId]['total'] &&
          this.pointsDatas[matiereId]['periodes'][periodeId]['certificated']) {

          const pempId = this.pointsDatas[matiereId]['periodes'][periodeId]['pempId']
          const isCode = this.codes[parseFloat(this.pemps[pempId])] ? true : false
          const ignored = isCode ? this.codes[parseFloat(this.pemps[pempId])]['ignored'] : false

          if (!ignored) {
            total += parseInt(this.pointsDatas[matiereId]['periodes'][periodeId]['total'])
            points += this.pemps[pempId] && !isCode ? parseFloat(this.pemps[pempId]) : 0
          }
        }
      }
    }

    if (inPercentage) {
      if (total > 0) {
        const ratio = total / 100
        percentage = Math.round((points / ratio) * 10) / 10
      }
      result = percentage
    } else {
      result = `${Math.round(points * 10) / 10} / ${total}`
    }

    return result
  }

  computePointsDiversTotal (pointDiversId, withTotal = true) {
    let points = 0
    let total = 0

    for (let periodeId in this.periodes) {
      for (let id in this.periodes[periodeId]['pointsDivers']) {
        if (id == pointDiversId) {
          const pepdpId = this.periodes[periodeId]['pointsDivers'][id]['pepdpId']

          if (this.periodes[periodeId]['pointsDivers'][id]['withTotal']) {
            total += this.periodes[periodeId]['pointsDivers'][id]['total']
          }

          if (pepdpId && this.pepdps[pepdpId]) {
            points += parseFloat(this.pepdps[pepdpId])
          }
        }
      }
    }

    if (withTotal && total > 0) {
      const ratio = total / 100
      points = Math.round((points / ratio) * 10) / 10
    }

    return points
  }

  computeTabNbCol () {
    let nbCol = 0

    for (let key in this.periodes) {
      nbCol++
    }

    return nbCol + 3
  }

  hasPointDivers (periodeId, pointDiversId) {
    let hasPointDivers = false

    if (this.periodes[periodeId]['pointsDivers'][pointDiversId] && this.periodes[periodeId]['pointsDivers'][pointDiversId]['pepdpId']) {
      hasPointDivers = true
    }

    return hasPointDivers
  }

  hasPointDiversTotal (pointDiversId) {
    const index = this.pointsDiversDatas.findIndex(pd => pd['id'] === pointDiversId)

    return (index > -1) && this.pointsDiversDatas[index]['withTotal']
  }

  getPepdpId (periodeId, pointDiversId) {
    return this.periodes[periodeId]['pointsDivers'][pointDiversId]['pepdpId']
  }

  getMatiereStyle (matiereId) {
    const green = '#8FD86A'
    const red = '#FF4C4C'
    let style = ''

    if (this.eleveMatiereOptions[matiereId]['deliberated']) {
      style = `background-color: ${green}`
    } else if (this.computeMatiereTotal(matiereId) < 50) {
      style = `background-color: ${red}`
    }

    return style
  }

  getPointDiversStyle (pointDiversId) {
    const red = '#FF4C4C'
    let style = ''

    if (this.hasPointDiversTotal(pointDiversId) && this.computePointsDiversTotal(pointDiversId) < 50) {
      style = `background-color: ${red}`
    }

    return style
  }

  getFinalTotalStyle () {
    const red = '#FF4C4C'
    let style = ''

    if (this.computeFinalTotal() < 50) {
      style = `background-color: ${red}`
    }

    return style
  }

  getCompletePrintPeriodeId () {
    let id = null

    for (let periodeId in this.periodes) {
      if (this.periodes[periodeId]['template'] === 'CompletePrint' || this.periodes[periodeId]['template'] === 'CompletePrintLarge') {
        id = periodeId
        break
      }
    }

    return id
  }

  print () {
    const periodeId = this.getCompletePrintPeriodeId()

    if (periodeId) {
      this.$http.get(Routing.generate('formalibreBulletinPrintElevePdf', {periode: periodeId, user: this.userId}))
    }
  }
}
