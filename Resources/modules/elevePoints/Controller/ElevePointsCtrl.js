import elevePointsConfirmationTemplate from '../Partial/eleve_points_confirmation_modal.html'

export default class ElevePointsCtrl {
  constructor ($http, $uibModal, ClarolineAPIService) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.ClarolineAPIService = ClarolineAPIService
    this.sessions = ElevePointsCtrl._getGlobal('sessions')
    this.pemps = ElevePointsCtrl._getGlobal('pemps')
    this.pepdps = ElevePointsCtrl._getGlobal('pepdps')
    this.codes = ElevePointsCtrl._getGlobal('codes')
    this.defaultCode = ElevePointsCtrl._getGlobal('defaultCode')
    this.isBulletinAdmin = ElevePointsCtrl._getGlobal('isBulletinAdmin')
    this.isPointOnly = ElevePointsCtrl._getGlobal('isPointOnly')
    this.hasSecondPoint = ElevePointsCtrl._getGlobal('hasSecondPoint')
    this.hasThirdPoint = ElevePointsCtrl._getGlobal('hasThirdPoint')
    this.secondPointName = ElevePointsCtrl._getGlobal('secondPointName')
    this.thirdPointName = ElevePointsCtrl._getGlobal('thirdPointName')
    this.periodeName = ElevePointsCtrl._getGlobal('periodeName')
    this.firstName = ElevePointsCtrl._getGlobal('firstName')
    this.lastName = ElevePointsCtrl._getGlobal('lastName')
    this.isLocked = {}
    this.sessions.forEach(s => this.isLocked[s['id']] = true)
    this.submit = this.submit.bind(this)
    this._removePempCallback = this._removePempCallback.bind(this)
  }

  _removePempCallback (d) {
    const sessionId = d['sessionId']
    const sessionIndex = this.sessions.findIndex(s => s['id'] === sessionId)

    if (sessionIndex > -1) {
      this.sessions.splice(sessionIndex, 1)
    }
    if (this.pemps[sessionId]) {
      delete this.pemps[sessionId]
    }
  }

  submit () {
    const url = Routing.generate('api_put_eleve_points')
    this.$http.put(url, {pemps: this.pemps, pepdps: this.pepdps}).then(d => {
      d['data']['pemps'].forEach(p => {
        const sessionId = parseInt(p['sessionId'])
        this.pemps[sessionId]['point'] = parseFloat(p['point'])
        this.pemps[sessionId]['presence'] = parseFloat(p['presence'])
        this.pemps[sessionId]['comportement'] = parseFloat(p['comportement'])
      })
      d['data']['pepdps'].forEach(p => {
        const pepdpId = parseInt(p['id'])
        const point = p['point'] ? parseFloat(p['point']) : null
        const index = this.pepdps.findIndex(pepdp => pepdp['id'] === pepdpId)

        if (index > -1) {
          this.pepdps[index]['point'] = point
        }
      })
    })
    this.sessions.forEach(s => this.isLocked[s['id']] = true)
  }

  validate () {
    if (this.isValidPemps()) {
      this.submit()
    } else {
      this.$uibModal.open({
        template: elevePointsConfirmationTemplate,
        controller: 'ElevePointsConfirmationModalCtrl',
        controllerAs: 'epc',
        resolve: {
          callback: () => { return this.submit },
          sessions: () => { return this.sessions },
          pemps: () => { return this.pemps },
          codes: () => { return this.codes },
          defaultCode: () => { return this.defaultCode },
          periodeName: () => { return this.periodeName },
          firstName: () => { return this.firstName },
          lastName: () => { return this.lastName },
          hasSecond: () => { return this.hasSecondPoint },
          hasThird: () => { return this.hasThirdPoint },
          secondName: () => { return this.secondPointName },
          thirdName: () => { return this.thirdPointName }
        }
      })
    }
  }

  deletePoint (pempId) {
    if (this.isBulletinAdmin) {
      const url = Routing.generate('formalibre_bulletin_pemp_delete', {pemp: pempId})
      this.ClarolineAPIService.confirm(
        {url, method: 'DELETE'},
        this._removePempCallback,
        Translator.trans('remove_course', {}, 'bulletin'),
        Translator.trans('remove_course_confirm_message', {}, 'bulletin')
      )
    }
  }

  switchLock (sessionId) {
    this.isLocked[sessionId] = !this.isLocked[sessionId]
  }

  isCode (value) {
    const index = this.codes.findIndex(c => parseInt(c['code']) === parseFloat(value))

    return index !== -1
  }

  isValidPoint (sessionId) {
    const point = parseFloat(this.pemps[sessionId]['point'])

    return (point >= 0 && point <= parseInt(this.pemps[sessionId]['total'])) || this.isCode(point)
  }

  isValidPresence (sessionId) {
    const presence = parseFloat(this.pemps[sessionId]['presence'])

    return (presence >= 0 && presence <= 100) || this.isCode(presence)
  }

  isValidComportement (sessionId) {
    const comportement = parseFloat(this.pemps[sessionId]['comportement'])

    return (comportement >= 0 && comportement <= 10) || this.isCode(comportement)
  }

  needTruncation (value, nbDecimals = 2) {
    const parts = value.toString().split('.')

    return !(parts.length < 2 || parts[1].length <= nbDecimals)
  }

  isValidPemps () {
    let isValid = true

    this.sessions.forEach(s => {
      if (!this.isValidPoint(s['id']) ||
        (this.hasSecondPoint && !this.isValidPresence(s['id'])) ||
        (this.hasThirdPoint && !this.isValidComportement(s['id'])) ||
        this.needTruncation(this.pemps[s['id']]['point']) ||
        (this.hasSecondPoint && this.needTruncation(this.pemps[s['id']]['presence'])) ||
        (this.hasThirdPoint && this.needTruncation(this.pemps[s['id']]['comportement']))) {

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
