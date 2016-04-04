export default class UsersCtrl {
  constructor ($http, ClarolineSearchService) {
    this.$http = $http
    this.ClarolineSearchService = ClarolineSearchService
    this.users = []
		this.savedSearch = []
		this.search = '[]'
		this.fields = ['firstName', 'lastName', 'classe']
    this.count = 0
    this.columns = [
      {
        name: Translator.trans('first_name', {}, 'platform'),
        prop: "firstName",
        cellRenderer: scope => {
          return `<a ui-sref="points({userId: ${scope.$row['id']}, groupId: '${scope.$row['groupId']}'})">${scope.$row['firstName']}</a>`
        }
      },
      {
        name: Translator.trans('last_name', {}, 'platform'),
        prop: "lastName",
        cellRenderer: scope => {
          return `<a ui-sref="points({userId: ${scope.$row['id']}, groupId: '${scope.$row['groupId']}'})">${scope.$row['lastName']}</a>`
        }
      },
      {
        name: Translator.trans('class', {}, 'bulletin'),
        prop: "groupName"
      }
    ]
		this.dataTableOptions = {
	 		scrollbarV: false,
	 		columnMode: 'force',
      headerHeight: 50,
      loadingMessage: Translator.trans('loading', {}, 'platform') + '...',
	 		columns: this.columns
	 	}
    this._onSearch = this._onSearch.bind(this)
    this.initialize()
  }

	paging (offset, size) {
    console.log(offset + ' - ' + size)
    this.dataTableOptions.paging.offset = offset
		//this.ClarolineSearchService.find('api_get_search_matiere_options', this.savedSearch, offset, size).then(d =>
		//	this.setCourseOptions(d.data, offset, size)
		//);
	}

  _onSearch(searches) {
    this.savedSearch = searches
    this.ClarolineSearchService.find('api_get_class_users', this.savedSearch, 0, 20).then(d => {
      this.users = d['data']
      this.dataTableOptions.paging.count = this.users.length
    })
  }

  initialize () {
    this.ClarolineSearchService.find('api_get_class_users', this.savedSearch, 0, 20).then(d => {
      this.users = d['data']
      this.dataTableOptions.paging.count = this.users.length
    })
  }
}