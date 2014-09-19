/*
 * This script uses PhantomJS to test user interaction with a server application
 * using the OneDrive SDK for PHP.
 *
 * The server application is also designed to test the OneDrive SDK for PHP.
 */

var fs       = require("fs")
var system   = require("system")
var LIVE     = JSON.parse(system.env["LIVE"])
var unitName = system.args[1]
var webpage  = require("webpage")
var page     = webpage.create()
var unit     = require("./unit/" + unitName)
var base     = "http://test.krizalys.com/onedrive-php-sdk/test/"
var unitBase = base + "unit/"
console.log("Testing " + unitName + "...")

/**
 * @param  (Function) onTestReady
 * @param  (Function) onReady
 * @param  (Function) onTimeout
 * @param  (Number) timeout - In milliseconds. Default: 10000.
 * @param  (Number) interval - In milliseconds. Default: 250.
 */
function wait(onTestReady, onReady, onTimeout, timeout, interval) {
	if (undefined === timeout) {
		timeout = 10000
	}

	if (undefined === interval) {
		interval = 250
	}

	var start = new Date().getTime()
	var ready = false

	var intervalId = setInterval(function() {
		if ((new Date().getTime() - start < timeout) && !ready) {
			ready = onTestReady()
		} else {
			clearInterval(intervalId)

			if (ready) {
				onReady()
			} else {
				onTimeout()
			}
		}
	}, interval)
}

function parseUrl(url) {
	var matches = url.match(/^((http[s]?|ftp):\/)?\/?([^:\/\s]+)([^#?\s]+)(\?(.*))?(#([\w\-]+))?$/)

	return {
		scheme  : matches[2],
		host    : matches[3],
		path    : matches[4],
		query   : undefined !== matches[6] ? matches[6] : null,
		fragment: undefined !== matches[8] ? matches[8] : null
	}
}

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
	var parsed = parseUrl(page.url)

	// Check whether we are on the login page and proceed.
	if ("login.live.com" == parsed.host && "/oauth20_authorize.srf" == parsed.path) {
		// Already done within page.open() callback.
	}

	// Check whether we are on the login form handler page and proceed.
	if ("login.live.com" == parsed.host && "/ppsecure/post.srf" == parsed.path) {
		// Nothing to do.
	}

	// Check whether we are on the account verification page and proceed.
	else if ("account.live.com" == parsed.host && "/identity/confirm" == parsed.path) {
		page.evaluate(function (email) {
			function getElementCenter(element) {
				var bounds = element.getBoundingClientRect()
				var left   = bounds.left
				var top    = bounds.top
				var right  = bounds.right
				var bottom = bounds.bottom
				var width  = right - left
				var height = bottom - top

				return {
					x: left + width / 2,
					y: top + height / 2
				}
			}

			function stripAtDomain(email) {
				var matches = email.match(/^([^@]+)@/)
				return matches[1]
			}

			var proof        = document.querySelector("form#frmProofs input#iProof2") // "Email xx*****@xxxxx.xxx"
			var confirm      = document.querySelector("form#frmProofs input[name=iConfirmProof]")
			var submit       = document.querySelector("form#frmProofs input#iNext")
			var proofCenter  = getElementCenter(proof)
			var submitCenter = getElementCenter(submit)
			var event

			// Check the proof radio button.
			event = document.createEvent("MouseEvent")
			event.initMouseEvent("click", true, true, window, 0, proofCenter.x, proofCenter.y, proofCenter.x, proofCenter.y, false, false, false, false, 0)
			proof.dispatchEvent(event)

			// Fill the email text field.
			confirm.value = stripAtDomain(email)

			// Click the "Send code button".
			event = document.createEvent("MouseEvent")
			event.initMouseEvent("click", true, true, window, 0, submitCenter.x, submitCenter.y, submitCenter.x, submitCenter.y, false, false, false, false, 0)
			submit.dispatchEvent(event)
		}, LIVE.email)

		console.log("After account verification, page content is:")
		console.log(page.content)
	}

	// Check whether we are on the authorization page and proceed.
	else if ("login.live.com" == parsed.host && "/oauth20_authorize.srf" == parsed.path) {
		// Nothing to do.
	}

	// Check whether we are back to our test application (ie. "code" must be
	// present in the query string).
	else if ("test.krizalys.com" == parsed.host && "/onedrive-php-sdk/test/" == parsed.path) {
		var queryString = parseQueryString(page.url)

		if ("code" in queryString) {
			unit(unitBase, function (status) {
				phantom.exit(status)
			})
		}
	}

	// Check whether we are on an unknown page and proceed.
	else {
		console.log("Reached unknown page: " + page.url + ", aborting. Content:")
		console.log(page.content)
		phantom.exit(1)
	}
}

page.open(base, function () {
	page.evaluate(function (data) {
		function getElementCenter(element) {
			var bounds = element.getBoundingClientRect()
			var left   = bounds.left
			var top    = bounds.top
			var right  = bounds.right
			var bottom = bounds.bottom
			var width  = right - left
			var height = bottom - top

			return {
				x: left + width / 2,
				y: top + height / 2
			}
		}

		var login        = document.querySelector("form[name=f1] input[name=login]")
		var password     = document.querySelector("form[name=f1] input[name=passwd]")
		var submit       = document.querySelector("form[name=f1] input[name=SI]")
		var submitCenter = getElementCenter(submit)
		var event        = document.createEvent("MouseEvent")
		login.value      = data.email
		password.value   = data.password
		event.initMouseEvent("click", true, true, window, 0, submitCenter.x, submitCenter.y, submitCenter.x, submitCenter.y, false, false, false, false, 0)
		submit.dispatchEvent(event)
	}, {
		email   : LIVE.email,
		password: LIVE.password
	})
})
