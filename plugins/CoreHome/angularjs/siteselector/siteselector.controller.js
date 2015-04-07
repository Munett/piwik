/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('SiteSelectorController', SiteSelectorController);

    SiteSelectorController.$inject = ['$scope', 'siteSelectorModel', 'piwik', 'AUTOCOMPLETE_MIN_SITES'];

    function SiteSelectorController($scope, siteSelectorModel, piwik, AUTOCOMPLETE_MIN_SITES){

        $scope.model = siteSelectorModel;

        $scope.autocompleteMinSites = AUTOCOMPLETE_MIN_SITES;
        $scope.selectedSite = {id: '', name: ''};
        $scope.activeSiteId = piwik.idSite;
        $scope.selectedSiteNameHtml = '';

        $scope.switchSite = function (site) {
            $scope.selectedSite = {id: site.idsite, name: site.name};

            if (!$scope.switchSiteOnSelect || $scope.activeSiteId == site.idsite) {
                return;
            }

            $scope.model.loadSite(site.idsite);
        };

        $scope.getUrlAllSites = function () {
            var newParameters = 'module=MultiSites&action=index';
            return piwik.helper.getCurrentQueryStringWithParametersModified(newParameters);
        };
        $scope.getUrlForSiteId = function (idSite) {
            var idSiteParam   = 'idSite=' + idSite;
            var newParameters = 'segment=&' + idSiteParam;
            var hash = piwik.broadcast.isHashExists() ? piwik.broadcast.getHashFromUrl() : "";
            return piwik.helper.getCurrentQueryStringWithParametersModified(newParameters) +
            '#' + piwik.helper.getQueryStringWithParametersModified(hash.substring(1), newParameters);
        };

        $scope.$watch('selectedSite', function (site) {
            if (!site.name) {
                return;
            }
            $scope.selectedSiteNameHtml = site.name.replace(/[\u0000-\u2666]/g, function(c) {
                return '&#'+c.charCodeAt(0)+';';
            });
        });

        siteSelectorModel.loadInitialSites();
    }
})();
