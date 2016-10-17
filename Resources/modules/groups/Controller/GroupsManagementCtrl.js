/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class GroupsManagementCtrl {
  constructor (NgTableParams, GroupService) {
    this.NgTableParams = NgTableParams
    this.GroupService = GroupService
    this.classes = GroupsManagementCtrl._getGlobal('classes')
    this.tableParams = new NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.classes}
    )
    this._addClassCallback = this._addClassCallback.bind(this)
    this._removeClassCallback = this._removeClassCallback.bind(this)
  }

  _addClassCallback (data) {
    this.classes.push(data)
    this.tableParams.reload()
  }

  _removeClassCallback (data) {
    const index = this.classes.findIndex(c => c['id'] === data['id'])

    if (index > -1) {
      this.classes.splice(index, 1)
      this.tableParams.reload()
    }
  }

  addClass () {
    this.GroupService.addClass(this._addClassCallback)
  }

  removeClass (groupId) {
    this.GroupService.removeClass(groupId, this._removeClassCallback)
  }

  static _getGlobal (name) {
    if (typeof window[name] === 'undefined') {
      throw new Error(
        `Expected ${name} to be exposed in a window.${name} variable`
      )
    }

    return window[name]
  }
}