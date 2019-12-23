$(function () {
    var list = new List('js-joblist', {
        page: 5,
        pagination: {
            outerWindow: 1,
        },
        valueNames: ['id', 'jobTitle', 'jobTitle2', 'subjectTitle', 'subjectTitle2', 'clinicTitle', 'city', 'city2', 'zipCode'],
        item: 'js-list-entry-template'
    }, jobs);
    var filterList = (function () {
        var $globalFilter = $('.js-search-form-input'),
            $city = $('.js-search-city')

        function _filterGlobal(job) {
            var globalVal = $globalFilter.val();
            // if (globalVal = '') {
            //     return true;
            // }

            var regExp = new RegExp(globalVal, 'i');
            return (job.jobTitle.search(regExp) != -1 ||
                job.subjectTitle.search(regExp) != -1 ||
                job.clinicTitle.search(regExp) != -1
            );
        }

        function _filterCityZip(job) {
            var val = $city.val();
            if (val == '') {
                return true;
            }

            var regExp = new RegExp(val, 'i');
            return (job.city.search(regExp) != -1 || job.zipCode.search(regExp) != -1);
        }

        function _filterFunction(item) {

        }

        var filter = function (item) {
            // var id = item.values().id;
            var job = item.values();
            return _filterGlobal(job) && _filterCityZip(job);
        };

        return function () {
            list.filter(); // filter leeren
            list.filter(filter);
        }
    })();
    $('.js-search-form-input, .js-search-city').on('input', function () {
        filterList();
    });

})