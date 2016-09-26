import SessionController from '../Controller/SessionController'

//claroline
import ClarolineSearch from '#/main/core/search/module'
import ClarolineAPI from '#/main/core/services/module'

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
