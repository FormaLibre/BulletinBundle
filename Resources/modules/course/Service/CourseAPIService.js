export default class CourseAPIService {
	constructor($http) {
		this.$http = $http
	}

	editPosition(id, position) {
		let route = Routing.generate('api_set_matiereoption_position', {'matiereOptions': id, 'position': position});
		this.$http.patch(route);
	}

	editTotal(id, total) {
		let route = Routing.generate('api_set_matiereoption_total', {'matiereOptions': id, 'total': total});
		this.$http.patch(route);
	}

	editColor(id, color) {
		let route = Routing.generate('api_set_matiereoption_color', {'matiereOptions': id, 'color': color});
		this.$http.patch(route);
	}
}