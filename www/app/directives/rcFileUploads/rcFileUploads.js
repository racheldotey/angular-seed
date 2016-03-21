'use strict';

/* 
 * Rachels Directives
 * 
 * A parent module for my custom directives.
 */

angular.module('rc.FileUploads', ['ngFileUpload', 'ngImgCrop'])
.directive('rcImageUploadWithEditor', function (DIRECTIVES_URL) {
    return {
        restrict: 'A',          // Must be a attribute on a html tag
        scope: {
            imageUpload: '=rcImageUploadWithEditor',
            options: '=?options'
        },
        templateUrl: DIRECTIVES_URL + 'rcFileUploads/imageUploadWithEditor.html',
        link: function ($scope, element, attributes) {
            // Link - Programmatically modify resulting DOM element instances, 
            // add event listeners, and set up data binding. 
            
            $scope.inputLabel = attributes.inputLabel || 'Image Upload';
            $scope.browseButtonLabel = attributes.browseButtonLabel || 'Browse';
            $scope.cropAreaLabel = attributes.cropAreaLabel || 'Crop Your Image';
            $scope.cropPreviewLabel = attributes.cropPreviewLabel || 'Preview';
            
            $scope.imageUpload = $scope.imageUpload || {};
            $scope.imageUpload.file = false;
            $scope.imageUpload.photostream = false;
            $scope.imageUpload.imageDataUrl = false;
            $scope.imageUpload.selectedFilesLabel = '';
        },
        controller: ["$scope", 'DIRECTIVES_URL', function ($scope, DIRECTIVES_URL) {
            // Controller - Create a controller which publishes an API for 
            // communicating across directives.            
            $scope.fileSelectionCallback = function($files, $file, $newFiles, $duplicateFiles, $invalidFiles, $event) {
                $scope.imageUpload.file = $file;
                $scope.imageUpload.selectedFilesLabel = ($files.length > 0) ? $file.name : '';
            };
        }]
    };
});