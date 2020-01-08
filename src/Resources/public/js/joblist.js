$(function () {
    var list = new List('js-joblist', {
        page: 5,
        pagination: {
            outerWindow: 1,
        },
        valueNames: ['id', 'jobTitle', 'jobTitle2', 'subjectTitle', 'subjectTitle2', 'clinicTitle', 'city', 'city2', 'zipCode', 'typeFulltime', 'typeParttime', 'typeLimited'],
        item: 'js-list-entry-template'
    }, jobs);

    // blende nach pagination / filter leere Elemente aus (Vollzeit / Teilzeit) ...
    list.on('updated', function (list) {
        console.log('updated')
        $(list.visibleItems).each(function (i, elem) {
            var $domnode = $(elem.elm);
            $domnode.find('.js-typefulltime, .js-typeparttime, .js-typelimited').each(function (i, elem) {
                var $elem = $(elem);
                if ($elem.text() == '') {
                    $elem.hide();
                } else {
                    $elem.show();
                }
            })
        })
    });

    // ...initiales Ausblenden 
    $('.js-typefulltime, .js-typeparttime, .js-typelimited').each(function (i, elem) {
        var $elem = $(elem);
        if ($elem.text() == '') {
            $elem.hide();
        } else {
            $elem.show();
        }
    });

    var filterList = (function () {
        var $globalFilter = $('.js-search-form-input'),
            $city = $('.js-search-city'),
            lat = 0, lon = 0;

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

    $('.js-search-city').on('input', function () {
        var zip = $(this).val();
        if (zip.length < 5) {
            return;
        }
        // frage https://nominatim.openstreetmap.org/search/?q=Germany,18146&format=json an
        var location = 'Germany,' + zip;
        // var geocode = 'https://open.mapquestapi.com/search?format=json&q=' + location;
        var geocode = 'https://nominatim.openstreetmap.org/search?format=json&q=' + location;
        $.getJSON(geocode, function (data) {
            // get lat + lon from first match
            if (!data.length) {
                // disabled
                $('.js-search-surrounding').prop('disabled', true);
                return;
            }
            $('.js-search-surrounding').prop('disabled', false);
            console.log(data);
            return;
            map.panTo(new L.LatLng(data[0].lat, data[0].lon));
            var latlng = [data[0].lat, data[0].lon]

            if (surroundingCircle) {
                map.removeLayer(surroundingCircle);
                surroundingCircle = null;
                $(".js-surrounding").val($(".js-surrounding option:first").val());
            }
            // console.log(latlng);
        });

    });

    $('.js-search-button').on('click', function (e) {
        e.preventDefault();
        // $('.js-search-form-input, .js-search-city').on('input', function () {
        filterList();
    });

    // click auf Liste --> Anzeige details
    // $('.js-resultlist__item').on('click', (function () {
    $('.js-joblist').on('click', '.js-resultlist__item', (function () {
        var $overlay = $('#overlay'),
            $closeButton = $('.js-close-overlay'),
            $jobTitle = $('#js-jobtitle'),
            $subjectTitle = $('#js-subjecttitle'),
            $city = $('#js-city'),
            $jobName = $('#js-jobname'),
            $jobDescription = $('#js-jobdescription'),
            $youOffer = $('#js-youoffer'),
            $weOffer = $('#js-weoffer'),
            $jobId = $('#js-jobid'),
            $clinicName = $('#clinicName'),
            // $clinicCity = $('#clinicCity'),
            $clinicAddress = $('#clinicAddress'),
            $clinicUrl = $('#clinicUrl'),
            $clinicBrochure = $('#clinicBrochure'),
            $clinicContactName = $('#clinicContactName'),
            $clinicContactDepartment = $('#clinicContactDepartment'),
            $clinicContactPhone = $('#clinicContactPhone'),
            $clinicContactMail = $('#clinicContactMail'),
            $jobMailto = $('.jobMailto'),
            $jobEquality = $('#jobEquality');

        $closeButton.on('click', function (e) {
            e.preventDefault();
            $overlay.css({
                'z-index': -5,
                'opacity': 0
            });
        })

        return function (e) {
            e.preventDefault();
            var $id = $(this).find('.id');
            if (!$id.length) {
                console.log('Job-Id nicht im Markup gefunden');
                return;
            }
            var id = $id.text();
            var jobIndex = jobMapping[id];
            if (jobIndex == undefined) {
                console.log('Job-Index nicht gefunden');
                return;
            }

            var job = jobs[jobIndex];
            console.log(job);
            setJobDetails(job);


            $overlay.css({
                'z-index': 1,
                'opacity': 1
            });
        }

        function setJobDetails(job) {
            $jobName.text(job.jobTitle + " - " + job.subjectTitle);
            $jobTitle.text(job.jobTitle);
            $city.text(job.city);
            $.each({
                'typeFulltime': '#js-typefulltime',
                'typeParttime': '#js-typeparttime',
                'typeLimited': '#js-typelimited'
            }, function (i, sel) {
                // checke gegen Inhalt/Länge
                if (job[i].length) {
                    $(sel).show();
                } else {
                    $(sel).hide();
                }
            })
            $subjectTitle.text(job.subjectTitle);
            $jobDescription.html(job.applicationNotes);
            $youOffer.html(job.youOffer);
            $weOffer.html(job.weOffer);
            $jobId.text(job.jobID);
            $clinicName.text(job.clinicTitle);
            // $clinicCity.text(job.city)
            $clinicAddress.text(job.zipCode + " " + job.city);
            if (job.url != undefined) {
                $clinicUrl.attr('href', job.url);
            } else {
                $clinicUrl.hide();
            }
            if (job.brochure != undefined) {
                $clinicBrochure.attr('href', job.brochure);
            } else {
                $clinicBrochure.hide();
            }
            $clinicContactName.text(job.contactperson_title + " " + job.contactperson_firstname + " " + job.contactperson_lastname);
            $clinicContactDepartment.text(job.department);
            $clinicContactPhone.text(job.contactperson_phone).attr('href', 'tel:' + job.contactperson_phone);
            $clinicContactMail.text(job.contactperson_email).attr('href', 'mailto:' + job.contactperson_email);
            $jobMailto.attr('href', 'mailto:' + job.mailto);
            $jobEquality.html(job.equality);

        }
    })());

})