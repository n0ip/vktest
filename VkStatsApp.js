var VKStatsApp = angular.module( 'VKStatsApp', [ 'ngResource', 'ngRoute', 'ngResource' ] );

VKStatsApp.config( [ '$routeProvider',
	function($routeProvider) {
		$routeProvider.
		when('/', {
			templateUrl: '/templates/main.php',
			controller: 'mainPageCtrl'
		}).
		when('/project/:project_id', {
			templateUrl: '/templates/project.html',
			controller: 'projectsController'
		}).
		otherwise({
			redirectTo: '/'
		});
	}
] );

VKStatsApp.factory( 'sessionFactory', function( $resource ) {
	return {
		getSession: $resource( '/ajax.php?function=getSession' ),
		newSession: $resource( '/ajax.php?function=newSession' )
	};
});

VKStatsApp.directive('loadButtons', function () {
	return {
		restrict: 'E',
		templateUrl: '/templates/listButtons.html',
		scope: true
	};
});

VKStatsApp.controller( 'mainPageCtrl', function( $scope, $location, sessionFactory ) {

	sessionFactory.getSession.get({}, function( response ) {
		$scope.sid = response.sid;
		$scope.uid = response.uid;
	});

	$scope.newSession = function() {
		sessionFactory.newSession.get({}, function( response ) {
			$scope.sid = response.sid;
			$scope.uid = response.uid;
		});
	};

	$scope.isAllowed = function( pid, uid, event ) {
		event.preventDefault();
		VKStats.isAllowed( pid, uid, function( response ) {
			if( response.status !== 'ok' ) {
				//ToDo сделать нормальное flash-сообщение
				alert('Ошибка доступа: Вы не добавлены в этот проект.');
			} else {
				$scope.$apply( function() {
					$location.path( '/project/' + pid );
				});
			};
		});
	};

	VKStats.getProjects( function( response ) {
		$scope.projects = response.rsp;
		$scope.$apply();
	});
	
});

VKStatsApp.controller( 'projectsController', function( $scope, $routeParams, sessionFactory ) {
	
	sessionFactory.getSession.get({}, function( response ) {
		VKStats.isCompleted( $routeParams.project_id, response.uid, function ( response ) {			
			if( response.completed === true ) {
				$scope.completed = true;
				$scope.$apply();
			}
		} );
	});

	VKStats.getActions( $routeParams.project_id, function( response ) {
		$scope.actions = response.rsp;
	});

});