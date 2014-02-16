angular.module("plex-wwwatch",
    [
        "ngRoute",
        "ngResource",
        "angularMoment",
        "ngTable",
        "PlexWWWatchPartials",
        "base64",
        "plex",
        "LocalStorageModule",
        "ngPlexWatch"
    ])
.service("PWWWService", function ($http, $q) {
    this.check = function () {
        var deferred = $q.defer();
        $http.get("backend/check.php").success(function (data) {
            if (data.length <= 0) {
                deferred.resolve();
            } else {
                deferred.reject(data);
            }
        });

        return deferred.promise;
    };

    this.getSettings = function () {
        var deferred = $q.defer();
        $http.get("backend/settings.php").success(function (data) {
            deferred.resolve(data);
        });

        return deferred.promise;
    };

    this.saveSettings = function (settings) {
        var deferred = $q.defer();
        $http.post("backend/settings.php", settings).success(function (data) {
            deferred.resolve(data);
        });

        return deferred.promise;
    };
})
.factory("plexWWWatchConstants", function () {
    return {
        deviceIcons: {
            "Plex Home Theater": "device-icon-pht",
            "Firefox": "device-icon-firefox",
            "Chrome": "device-icon-chrome",
            "Internet Explorer": "device-icon-ie",
            "iOS": "device-icon-ios",
            "Android": "device-icon-android"
        },
        typeIcons: {
            "episode": "glyphicon display",
            "movie": "glyphicon film"
        }
    };
})
.config(["$routeProvider", function ($routeProvider) {
    $routeProvider
        .when("/home", {
            controller: "HomeCtrl",
            templateUrl: "partials/home.html"
        })
        .when("/settings", {
            controller: "SettingsCtrl",
            templateUrl: "partials/settings.html"
        })
        .when("/users", {
            controller: "UsersCtrl",
            templateUrl: "partials/users.html"
        })
        .when("/users/:user", {
            controller: "UserCtrl",
            templateUrl: "partials/user.html"
        })
        .when("/plex", {
            controller: "PlexCtrl",
            templateUrl: "partials/plex.html"
        })
        .when("/check", {
            controller: "CheckCtrl",
            templateUrl: "partials/check.html"
        })
        .otherwise({ redirectTo: "/check" })
        ;
}])
.run(function ($rootScope, PWWWService, localStorageService) {
    PWWWService.getSettings().then(function (settings) {
        $rootScope.settings = settings;
    });

    $rootScope.plex = {
        token: localStorageService.get("plexToken")
    };
})
;


