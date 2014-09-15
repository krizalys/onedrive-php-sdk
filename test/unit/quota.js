module.exports = function (baseUrl, callback) {
	var page = require("webpage").create()

	page.open(baseUrl + "quota.php", function () {
		var data = JSON.parse(page.plainText)

		if (!("quota" in data)) {
			callback(1)
			return
		}

		if (!("available" in data)) {
			callback(1)
			return
		}

		if (data.quota < data.available) {
			callback(1)
			return
		}

		callback(0)
	})
}
