<button ng-click="newSession()">Обновить сессию</button><br />
SID: {{ sid }}<br />
UID: {{ uid }}<br />

Перейти к проекту: <br />

<div ng-repeat="(id, project) in projects">
	<a ng-href="#/project/{{ id }}" ng-click="isAllowed( id, uid, $event )">{{ project }}</a>
</div>
