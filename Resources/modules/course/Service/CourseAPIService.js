export default class CourseAPIService {
	constructor($http) {
		this.$http = $http
	}

	editPosition(id, displayOrder) {
		const route = Routing.generate('api_put_session_display_order', {session: id, displayOrder: displayOrder})
		this.$http.put(route)
	}

	editTotal(id, total) {
    const route = Routing.generate('api_put_session_total', {'session': id, 'total': total})
		this.$http.put(route)
	}

	editColor(id, color) {
    const route = Routing.generate('api_put_session_color', {'session': id, 'color': color})
		this.$http.put(route)
	}
}