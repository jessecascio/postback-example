
/**
 * Monitor the job queue
 */
(function () {

	var url     = require('url');
	var query   = require('querystring');
	var redis   = require('redis');
	var request = require('request');

	client = redis.createClient();

	var pop = function () {

		// prior to pop, verify the "active" token is set, allows graceful shutdown
		
		client.rpop('job-queue', function (e, r) {
			var error = {
				'key': new Date().getTime() // test if unique enough
			};

			// log any errors
			if (e) {
				error.msg = e;
				client.zadd(['error-log', new Date().getTime(), JSON.stringify(error)], function (e,r) {});
				return;
			}

			if (r) {
				var json = JSON.parse(r);

				if (typeof json.url === "undefined" || !json.url) {
					// could log this as well
					return;
				}
				if (typeof json.method === "undefined" || !json.method) {
					// could log this as well
					return;
				}
				if (typeof json.data === "undefined" || !json.data) {
					// could log this as well
					return;
				}

				var endpoint  = url.parse(json.url); // endpoint break down
				var structure = query.parse(endpoint.query);

				var load = {};

				// determine data pieces we have for structure
				for (var i in structure) {
					load[i] = typeof json.data[i] !== "undefined" && json.data[i] ? json.data[i] : '';
				}

				var data = {
				    url: endpoint.protocol + '//' + endpoint.host + endpoint.pathname, 
				    qs: load, 
				    method: json.method,
				};

				// add iteration here to try X amount of times
				
				// push the call out, could we batch request ???
				request(data, function(e, r, body){
				    if(e) {
				        error.msg = e;
						client.zadd(['error-log', new Date().getTime(), JSON.stringify(error)], function (e,r) {});
				    } else {
				    	// success
				    	var success = {
				    		'key': new Date().getTime(), // test if unique enough
				    		'status': r.statusCode,
				    		'response': body,
				    		'request': data.url,
				    		'data': data.qs
				    	};
				        client.zadd(['request-log', new Date().getTime(), JSON.stringify(success)], function (e,r) {});
				    }
				});
			}
		});
	}

	// continuously loop
	setInterval(pop, 0);

})();
