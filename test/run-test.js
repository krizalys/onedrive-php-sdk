/*
 * This script uses PhantomJS to test user interaction with a server application
 * using the OneDrive SDK for PHP.
 *
 * The server application is also designed to test the OneDrive SDK for PHP.
 */

var system   = require("system")
var LIVE     = JSON.parse(system.env["LIVE"])
var unitName = system.args[1]
var page     = require("webpage").create()
var unit     = require("./unit/" + unitName)
var base     = "http://test.krizalys.com/onedrive-php-sdk/test/"
var unitBase = base + "unit/"
console.log("Testing " + unitName + "...")

function parseQueryString(url) {
	var parts = url.split("?")

	if (2 > parts.length) {
		return {}
	}

	var queryString = {}

	parts[1].split("&").forEach(function (keyValue) {
		var pair         = keyValue.split("=")
		var key          = pair[0]
		var value        = pair[1]
		queryString[key] = value
	})

	return queryString
}

page.onLoadFinished = function (status) {
	// Checks whether we are back to our test application (ie. "code" must be
	// present in the query string).
	var queryString = parseQueryString(page.url)

	if ("code" in queryString) {
		unit(unitBase, function (status) {
			phantom.exit(status)
		})
	}
}

page.open(base, function () {
	page.evaluate(function (data) {
		var login        = document.querySelector("form[name=f1] input[name=login]")
		var password     = document.querySelector("form[name=f1] input[name=passwd]")
		var submit       = document.querySelector("form[name=f1] input[name=SI]")
		var submitBounds = submit.getBoundingClientRect()
		var left         = submitBounds.left
		var top          = submitBounds.top
		var right        = submitBounds.right
		var bottom       = submitBounds.bottom
		var width        = right -left
		var height       = bottom - top
		var x            = left + width / 2
		var y            = top + height / 2
		var event        = document.createEvent("MouseEvent")
		login.value      = data.email
		password.value   = data.password
		event.initMouseEvent("click", true, true, window, 0, x, y, x, y, false, false, false, false, 0)
		submit.dispatchEvent(event)
	}, {
		email   : LIVE.email,
		password: LIVE.password
	})
})
