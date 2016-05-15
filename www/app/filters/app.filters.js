'use strict';

/* 
 * Custom filters used by the app
 * 
 * Include controllers and other modules required on the un authenticated pages.
 */

var app = angular.module('app.filters', [
    'app.filters.number',
    'app.filters.string'
]);

// http://stackoverflow.com/questions/14478106/angularjs-sorting-by-property
app.filter('orderObjectBy', function () {
    
    var sortStringAsc = function(array, attribute) {
        array.sort(function (a, b) {
            if (a[attribute] < b[attribute])
                return -1;
            if (a[attribute] > b[attribute])
                return 1;
            return 0;
        });
        return array;
    };
    
    var sortStringDesc = function(array, attribute) {
        array.sort(function (a, b) {
            if (a[attribute] < b[attribute])
                return 1;
            if (a[attribute] > b[attribute])
                return -1;
            return 0;
        });
        return array;
    };
    
    var sortIntAsc = function(array, attribute) {
        array.sort(function (a, b) {
            a = parseInt(a[attribute]);
            b = parseInt(b[attribute]);
            return a - b;
        });
        return array;
    };
    
    var sortIntDesc = function(array, attribute) {
        array.sort(function (a, b) {
            a = parseInt(a[attribute]);
            b = parseInt(b[attribute]);
            return b - a;
        });
        return array;
    };
    
    var sortFloatAsc = function(array, attribute) {
        array.sort(function (a, b) {
            a = parseFloat(a[attribute]);
            b = parseFloat(b[attribute]);
            return a - b;
        });
        return array;
    };
    
    var sortFloatDesc = function(array, attribute) {
        array.sort(function (a, b) {
            a = parseFloat(a[attribute]);
            b = parseFloat(b[attribute]);
            return b - a;
        });
        return array;
    };
    
    var sortForTypeAsc = function(type, array, attribute) {
        switch (type.toLowerCase()) {
            case 'string':
                array = sortStringAsc(array, attribute);
                break;
            case 'float':
                array = sortFloatAsc(array, attribute);
                break;
            case 'int':
            default:
                array = sortIntAsc(array, attribute);
                break;
        }
        return array;
    };
    
    var sortForTypeDesc = function(type, array, attribute) {
        switch (type.toLowerCase()) {
            case 'string':
                array = sortStringDesc(array, attribute);
                break;
            case 'float':
                array = sortFloatDesc(array, attribute);
                break;
            case 'int':
            default:
                array = sortIntDesc(array, attribute);
                break;
        }
        return array;
    };
    
    return function (input, attribute, type, direction) {
        if (!angular.isObject(input))
            return input;

        var array = [];
        for (var objectKey in input) {
            array.push(input[objectKey]);
        }
        
        switch(direction.toLowerCase()) {
            case 'asc':
                array = sortForTypeAsc(type, array, attribute);
                break;
            case 'desc':
            default:
                array = sortForTypeDesc(type, array, attribute);
                break;
        }
        return array;
    };
});