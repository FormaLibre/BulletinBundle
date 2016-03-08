import SessionController from '../Controller/SessionController'

//claroline
import ClarolineSearch from '../../../../../../claroline/core-bundle/Resources/modules/search/module'
import ClarolineAPI from '../../../../../../claroline/core-bundle/Resources/modules/services/module'

export default class SessionManagerDirective {
    constructor() {
        this.restrict = 'E';
        this.template = require('../Partial/session_manager.html');
        this.replace = false;
        this.controller = SessionController
        this.controllerAs = 'sc'
    }
}

SessionController.$inject = ['$http', 'ClarolineSearchService', 'ClarolineAPIService', '$cacheFactory']
