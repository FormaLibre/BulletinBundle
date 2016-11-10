import matierePointsConfirmationTemplate from '../Partial/matiere_points_confirmation_modal.html'

export default class MatierePointsCtrl {
  constructor ($http, $uibModal) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.isLocked = true
    this.users = MatierePointsCtrl._getGlobal('users')
    this.pemps = MatierePointsCtrl._getGlobal('pemps')
    this.codes = MatierePointsCtrl._getGlobal('codes')
    this.defaultCode = MatierePointsCtrl._getGlobal('defaultCode')
    this.isPointOnly = MatierePointsCtrl._getGlobal('isPointOnly')
    this.hasSecondPoint = MatierePointsCtrl._getGlobal('hasSecondPoint')
    this.hasThirdPoint = MatierePointsCtrl._getGlobal('hasThirdPoint')
    this.secondPointName = MatierePointsCtrl._getGlobal('secondPointName')
    this.thirdPointName = MatierePointsCtrl._getGlobal('thirdPointName')
    this.periodeName = MatierePointsCtrl._getGlobal('periodeName')
    this.courseTitle = MatierePointsCtrl._getGlobal('courseTitle')
    this.sessionName = MatierePointsCtrl._getGlobal('sessionName')
    this.submit = this.submit.bind(this)
  }

  submit () {
    const url = Routing.generate('api_put_matiere_points')
    this.$http.put(url, {points: this.pemps}).then(d => {
      d['data'].forEach(p => {
        const userId = parseInt(p['eleveId'])
        this.pemps[userId]['point'] = parseFloat(p['point'])
        this.pemps[userId]['presence'] = parseFloat(p['presence'])
        this.pemps[userId]['comportement'] = parseFloat(p['comportement'])
      })
    })
    this.isLocked = true
  }

  validate () {
    if (this.isValidPemps()) {
      this.submit()
    } else {
      this.$uibModal.open({
        template: matierePointsConfirmationTemplate,
        controller: 'MatierePointsConfirmationModalCtrl',
        controllerAs: 'mpc',
        resolve: {
          callback: () => { return this.submit },
          users: () => { return this.users },
          pemps: () => { return this.pemps },
          codes: () => { return this.codes },
          defaultCode: () => { return this.defaultCode },
          periodeName: () => { return this.periodeName },
          courseTitle: () => { return this.courseTitle },
          sessionName: () => { return this.sessionName },
          hasSecond: () => { return this.hasSecondPoint },
          hasThird: () => { return this.hasThirdPoint },
          secondName: () => { return this.secondPointName },
          thirdName: () => { return this.thirdPointName }
        }
      })
    }
  }

  unlock () {
    this.isLocked = false
  }

  isCode (value) {
    const index = this.codes.findIndex(c => parseInt(c['code']) === parseFloat(value))

    return index !== -1
  }

  isValidPoint (userId) {
    const point = parseFloat(this.pemps[userId]['point'])

    return (point >= 0 && point <= parseInt(this.pemps[userId]['total'])) || this.isCode(point)
  }

  isValidPresence (userId) {
    const presence = parseFloat(this.pemps[userId]['presence'])

    return (presence >= 0 && presence <= 100) || this.isCode(presence)
  }

  isValidComportement (userId) {
    const comportement = parseFloat(this.pemps[userId]['comportement'])

    return (comportement >= 0 && comportement <= 10) || this.isCode(comportement)
  }

  needTruncation (value, nbDecimals = 2) {
    const parts = value.toString().split('.')

    return !(parts.length < 2 || parts[1].length <= nbDecimals)
  }

  isValidPemps () {
    let isValid = true

    this.users.forEach(u => {
      if (!this.isValidPoint(u['id']) ||
        (this.hasSecondPoint && !this.isValidPresence(u['id'])) ||
        (this.hasThirdPoint && !this.isValidComportement(u['id'])) ||
        this.needTruncation(this.pemps[u['id']]['point']) ||
        (this.hasSecondPoint && this.needTruncation(this.pemps[u['id']]['presence'])) ||
        (this.hasThirdPoint && this.needTruncation(this.pemps[u['id']]['comportement']))) {

        isValid = false
      }
    })

    return isValid
  }

  static _getGlobal (name) {
    if (typeof window[name] === 'undefined') {
      throw new Error(`Expected ${name} to be exposed in a window.${name} variable`)
    }

    return window[name]
  }
}
