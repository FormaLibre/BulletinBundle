/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class GroupsSelectionModalCtrl {
  constructor(NgTableParams, GroupService, callback) {
    this.GroupService = GroupService
    this.callback = callback
    this.groups = []
    this.tableParams = new NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.groups}
    )
    this.initialize()
  }

  initialize () {
    this.GroupService.loadUntaggedGroups().then(d => {
      d.forEach(g => this.groups.push(g))
    })
  }

  createClass (groupId) {
    this.GroupService.createClass (groupId).then(d => {
      const index = this.groups.findIndex(g => g['id'] === d['id'])

      if (index > -1) {
        this.groups.splice(index, 1)
        this.tableParams.reload()
      }
      if (this.callback) {
        this.callback(d)
      }
    })
  }
}
