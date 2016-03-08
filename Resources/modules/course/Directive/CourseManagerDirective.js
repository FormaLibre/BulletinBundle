import CourseController from '../Controller/CourseController'
import CourseAPIService from '../Service/CourseAPIService'

//claroline
import ClarolineSearch from '../../../../../../../../claroline/core-bundle/Resources/modules/search/module'
import ClarolineAPI from '../../../../../../../../claroline/core-bundle/Resources/modules/services/module'

export default class CourseManagerDirective {
    constructor() {
        this.restrict = 'E';
        this.template = require('../Partial/course_manager.html');
        this.replace = false;
        this.controller = CourseController
        this.controllerAs = 'cmc'
    }
}

CourseController.$inject = ['$http', 'ClarolineSearchService', 'ClarolineAPIService', 'CourseAPIService','$cacheFactory']
