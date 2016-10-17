/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/
/*global Translator*/
import angular from 'angular/index'
import groupsSelectionTemplate from '../Partial/groups_selection_modal.html'

export default class GroupService {
  constructor ($http, $uibModal, ClarolineAPIService) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.ClarolineAPIService = ClarolineAPIService
  }

  addClass (callback = null) {
    this.$uibModal.open({
      template: groupsSelectionTemplate,
      controller: 'GroupsSelectionModalCtrl',
      controllerAs: 'gmc',
      resolve: {
        callback: () => { return callback }
      }
    })
  }

  removeClass (groupId, callback = null) {
    const url = Routing.generate('formalibre_bulletin_group_remove_class_tag', {group: groupId})
    this.ClarolineAPIService.confirm(
      {url, method: 'DELETE'},
      callback,
      Translator.trans('remove_class', {}, 'bulletin'),
      Translator.trans('remove_class_confirm_message', {}, 'bulletin')
    )
  }

  createClass (groupId, callback = null) {
    const url = Routing.generate('formalibre_bulletin_group_tag_as_class', {group: groupId})

    return this.$http.post(url).then(d => {
      if(d['status'] === 200) {
        return d['data']
      }
    })
  }

  loadUntaggedGroups () {
    const url = Routing.generate('api_get_untagged_groups')

    return this.$http.get(url).then(d => {
      if(d['status'] === 200) {
        return d['data']
      }
    })
  }
}