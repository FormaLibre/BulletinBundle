/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class MatierePointsConfirmationModalCtrl {
  constructor ($uibModalInstance, callback, users, pemps, codes, defaultCode, periodeName, courseTitle, sessionName, hasSecond, hasThird, secondName, thirdName) {
    this.$uibModalInstance = $uibModalInstance
    this.callback = callback
    this.users = users
    this.pemps = pemps
    this.codes = codes
    this.defaultCode = defaultCode
    this.periodeName = periodeName
    this.courseTitle = courseTitle
    this.sessionName = sessionName
    this.hasSecond = hasSecond
    this.hasThird = hasThird
    this.secondName = secondName
    this.thirdName = thirdName
    this.toDefault = []
    this.outOfBounds = []
    this.toTruncate = []
    this.checkValues()
  }

  checkValues () {
    this.users.forEach(u => {
      let hasDefaultError = false
      let hasOutError = false
      let hasTruncateError = false
      let defaultRow = {name: `${u['firstName']} ${u['lastName']}`, isPoint: true, isPresence: true, isComportement: true}
      let outRow = {name: `${u['firstName']} ${u['lastName']}`, isPoint: true, isPresence: true, isComportement: true}
      let truncateRow = {name: `${u['firstName']} ${u['lastName']}`, isPoint: true, isPresence: true, isComportement: true}
      const point = this.pemps[u['id']]['point']
      const presence = this.pemps[u['id']]['presence']
      const comportement = this.pemps[u['id']]['comportement']

      if (point === undefined || point === null) {
        defaultRow['isPoint'] = false
        hasDefaultError = true
      } else {
        defaultRow['point'] = point
        outRow['point'] = point
        truncateRow['point'] = point
        const truncatedPoint = this.truncate(point)

        if (!this.isValidPoint(point, this.pemps[u['id']]['total'])) {
          outRow['isPoint'] = false
          hasOutError = true
        }
        if (truncatedPoint !== point) {
          hasTruncateError = true
          truncateRow['isPoint'] = false
          truncateRow['point'] = truncatedPoint
        }
      }
      if (this.hasSecond) {
        if (presence === undefined || presence === null) {
          defaultRow['isPresence'] = false
          hasDefaultError = true
        } else {
          defaultRow['presence'] = presence
          outRow['presence'] = presence
          truncateRow['presence'] = presence
          const truncatedPresence = this.truncate(presence)

          if (!this.isValidPresence(presence)) {
            outRow['isPresence'] = false
            hasOutError = true
          }
          if (truncatedPresence !== presence) {
            hasTruncateError = true
            truncateRow['isPresence'] = false
            truncateRow['presence'] = truncatedPresence
          }
        }
      }
      if (this.hasThird) {
        if  (comportement === undefined || comportement === null) {
          defaultRow['isComportement'] = false
          hasDefaultError = true
        } else {
          defaultRow['comportement'] = comportement
          outRow['comportement'] = comportement
          truncateRow['comportement'] = comportement
          const truncatedComportement = this.truncate(comportement)

          if (!this.isValidComportement(comportement)) {
            outRow['isComportement'] = false
            hasOutError = true
          }
          if (truncatedComportement !== comportement) {
            hasTruncateError = true
            truncateRow['isComportement'] = false
            truncateRow['comportement'] = truncatedComportement
          }
        }
      }
      if (hasDefaultError) {
        this.toDefault.push(defaultRow)
      }
      if (hasOutError) {
        this.outOfBounds.push(outRow)
      }
      if (hasTruncateError) {
        this.toTruncate.push(truncateRow)
      }
    })
  }

  isCode (value) {
    const index = this.codes.findIndex(c => parseInt(c['code']) === parseFloat(value))

    return index !== -1
  }

  isValidPoint (point, total) {
    return (point >= 0 && point <= total) || this.isCode(point)
  }

  isValidPresence (presence) {
    return (presence >= 0 && presence <= 100) || this.isCode(presence)
  }

  isValidComportement (comportement) {
    return (comportement >= 0 && comportement <= 10) || this.isCode(comportement)
  }

  truncate (value, nbDecimals = 2) {
    const parts = value.toString().split('.')

    if (parts.length < 2 || parts[1].length <= nbDecimals) {
      return value
    } else {
      const newDecimalPart = parts[1].slice(0, nbDecimals)

      return parseFloat(`${parts[0]}.${newDecimalPart}`)
    }
  }

  confirm () {
    this.callback()
    this.$uibModalInstance.close()
  }
}
