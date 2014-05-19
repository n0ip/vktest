if ( !window.jQuery ) {
    alert("Подключите библиотеку jQuery 1.1.11");
	throw new Error("Подключите библиотеку jQuery 1.1.11");
}

var VKStats = {
	getProjects: function( callback ) {
		var request = $.ajax({
			url: "/ajax.php",
			type: "GET",
			data: { function: 'getProjects' },
			dataType: "json"
		});

		request.done(function( response ) {
			callback( response );
		});

		request.fail(function( jqXHR, textStatus ) {
			alert( "Request failed: " + textStatus );
		});
	},
	isAllowed: function( pid, uid, callback ) {
		var request = $.ajax({
			url: "/ajax.php",
			type: "GET",
			data: { function: 'isAllowed', pid : pid, uid : uid },
			dataType: "json"
		});

		request.done(function( response ) {
			callback( response );
		});
	},
	isCompleted: function( pid, uid, callback ) {
		var request = $.ajax({
			url: "/ajax.php",
			type: "GET",
			data: { function: 'isCompleted', pid : pid, uid : uid },
			dataType: "json"
		});

		request.done(function( response ) {
			callback( response );
		});
	},
	getActions: function( pid, callback ) {
		var request = $.ajax({
			url: "/ajax.php",
			type: "GET",
			data: { function: 'getActions', pid : pid },
			dataType: "json"
		});

		request.done(function( response ) {
			callback( response );
		});
	},
	trackAction: function( params ) {
		var request = $.ajax({
			url: "/ajax.php",
			type: "GET",
			data: { function: 'trackAction', action : params.action, pid : params.pid, uid : params.uid },
			dataType: "json"
		});
		
		request.done(function( response ) {
			params.callback( response );
		});
	}
};
