//查看列表


(function(app){
	app.controller('ViewCtrl',['$scope','$location', 'NoteApi',function($scope,$location,NoteApi){

		var reportId = $location.search().note_id;
		$scope.resDateil="";
		NoteApi.getNoteDetai({
			"note_id" : reportId
		}).then(function(data){
			if(data.errcode ==0 ){
				$scope.resDateil = data.result;
			}
		})

	}]);
})(angular.module('app.modules.notelist'));
