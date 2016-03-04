export default class SessionManagerDirective {
    constructor() {
        this.restrict = 'E';
        this.template = require('../Partial/session_manager.html');
        this.replace = false;
        this.controller = SessionController
        this.controllerAs = 'sc'
    }
}
