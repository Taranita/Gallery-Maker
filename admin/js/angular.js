
// Get images data
var app = angular.module("myApp", [])
    .controller('imagesCtrl', function($scope, $http) {
        try {
            $http.get("images_mysql.php")
            .then(function (response) {$scope.image_data = response.data.images;})
        } catch(error){
            console.log('Error is handled: ', error.name);
        }
    })
    .factory('$exceptionHandler', function() {
    return function(exception, cause) {
        console.log(exception)
    }
});
