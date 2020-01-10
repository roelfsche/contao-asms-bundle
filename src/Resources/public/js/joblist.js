$(function () {
    var $filterGlobal = $('.js-search-form-input'),
        $filterCity = $('.js-search-city'),
        $filterTypeSelect = $('#js-filter-function'),
        $filterSubject = $('#js-filter-subject'),
        $filterJobId = $('.js-filter-jobid'),
        $filterSurrounding = $('.js-search-surrounding'),
        $filterSearchButton = $('.js-search-button'),
        $filterResetButton = $('.js-reset-filter');
    var latLon = { lat: 0, lon: 0 };



    var list = new List('js-joblist', {
        page: 5,
        pagination: {
            outerWindow: 1,
        },
        valueNames: ['id', 'jobTitle', 'jobTitle2', 'jobType', 'jobSubject', 'jobId', 'subjectTitle', 'subjectTitle2', 'clinicTitle', 'city', 'city2', 'zipCode', 'typeFulltime', 'typeParttime', 'typeLimited'],
        item: 'js-list-entry-template'
    }, jobs);

    // blende nach pagination / filter leere Elemente aus (Vollzeit / Teilzeit) ...
    list.on('updated', function (list) {
        // console.log('updated')
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
        function _filterGlobal(job) {
            var globalVal = $filterGlobal.val();
            // if (globalVal = '') {
            //     return true;
            // }

            var regExp = new RegExp(globalVal, 'i');
            return (job.jobTitle.search(regExp) != -1 ||
                job.subjectTitle.search(regExp) != -1 ||
                job.clinicTitle.search(regExp) != -1
            );
        }

        function _filterByCityZip(job) {
            var val = $filterCity.val();
            if (val == '' || parseInt($filterSurrounding.val()) != 0) {
                return true;
            }

            var regExp = new RegExp(val, 'i');
            return (job.city.search(regExp) != -1 || job.zipCode.search(regExp) != -1);
        }

        function _filterBySurrounding(job) {
            var filterVal = parseInt($filterSurrounding.val());
            if (filterVal == 0) {
                return true;
            }
            if (latLon.lat == 0 || latLon.lon == 0) {
                return true;
            }
            var lat = parseFloat(job.lat),
                lon = parseFloat(job.lon);
            if (!lat || !lon) {
                return false;
            }
            var dist = distance(latLon.lat, latLon.lon, lat, lon, 'K');
            return (dist <= filterVal);
        }

        function _filterByFunction(job) {
            var filterVal = parseInt($filterTypeSelect.val());
            if (!filterVal) {
                return true;
            }
            return job.jobType == filterVal;
        }

        function _filterBySubject(job) {
            var filterVal = parseInt($filterSubject.val());
            if (!filterVal) {
                return true;
            }
            return job.jobSubject == filterVal;
        }

        function _filterByJobId(job) {
            var filterVal = $filterJobId.val();
            if (!filterVal.length) {
                return true;
            }
            return job.jobId == filterVal;
        }

        function _filterByJobKind(job) {
            var $filterElem = $("input[name='job_kind[]']:checked");
            if ($filterElem.length == 0) {
                return true;
            }
            var filterVal = $filterElem.val();
            return job[filterVal] != "";
        }

        var filter = function (item) {
            // var id = item.values().id;
            var job = item.values();
            return _filterGlobal(job)
                && _filterByCityZip(job)
                && _filterBySurrounding(job)
                && _filterByFunction(job)
                && _filterBySubject(job)
                && _filterByJobId(job)
                && _filterByJobKind(job)
                ;
        };

        function distance(lat1, lon1, lat2, lon2, unit) {
            if ((lat1 == lat2) && (lon1 == lon2)) {
                return 0;
            }
            else {
                var radlat1 = Math.PI * lat1 / 180;
                var radlat2 = Math.PI * lat2 / 180;
                var theta = lon1 - lon2;
                var radtheta = Math.PI * theta / 180;
                var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
                if (dist > 1) {
                    dist = 1;
                }
                dist = Math.acos(dist);
                dist = dist * 180 / Math.PI;
                dist = dist * 60 * 1.1515;
                if (unit == "K") { dist = dist * 1.609344 }
                if (unit == "N") { dist = dist * 0.8684 }
                return dist;
            }
        }

        return function () {
            list.filter(); // filter leeren
            list.filter(filter);
        }
    })();


    // ext. Filter rein / raus
    $('.js-show-extended-search-options').click(function (e) {
        e.preventDefault();
        $('.js-hide-options').hide();
        $('.js-search_more').show();
    });
    $('.js-hide-extended-search-options').click(function (e) {
        e.preventDefault();
        $('.js-hide-options').show();
        $('.js-search_more').hide();
    });

    // Direktliste Jobtypen
    $('.js-short-job-type').on('click', function (e) {
        e.preventDefault();
        $filterTypeSelect.val($(this).data('id'));
        $filterSearchButton.trigger('click');
    })

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
                latLon.lat = latLon.lon = 0;
                return;
            }
            latLon.lat = parseFloat(data[0].lat);
            latLon.lon = parseFloat(data[0].lon);
            $('.js-search-surrounding').prop('disabled', false);
            // console.log(data);
        });
    });


    $filterSearchButton.on('click', function (e) {
        e.preventDefault();
        filterList();
    });
    $filterResetButton.on('click', function (e) {
        e.preventDefault();
        $filterGlobal.val('');
        $filterCity.val('');
        $filterSurrounding.val(0);
        $filterSubject.val(0);
        $filterTypeSelect.val(0);
        $filterJobId.val('');
        $("input[name='job_kind[]']").prop('checked', false);
        $filterSearchButton.trigger('click')
    });


    // click auf Liste --> Anzeige details
    // $('.js-resultlist__item').on('click', (function () {
    $('.js-joblist').on('click', '.js-resultlist__item', (function () {
        var $overlay = $('#overlay'),
            $closeButton = $('.js-close-overlay'),
            $jobTitle = $('#js-jobtitle'),
            $subjectTitle = $('#js-subjecttitle'),
            $city = $('#js-city'),
            $logoWrapper = $('#js-cliniclogo-wrapper'),
            $logo = $('js-cliniclogo'),
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
            $awardDivWrapper = $('#js-award-div-wrapper'),
            $awardDivs = $('.js-award-div'),
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
            // console.log(job);
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
            if (job.clinicLogo != undefined) {
                $logoWrapper.show();
                $logo.attr('src', job.clinicLogo).attr('alt', 'clinicLogoAlt');
            } else {
                $logoWrapper.hide();
            }
            $youOffer.html(job.youOffer);
            $weOffer.html(job.weOffer);
            $jobId.text(job.jobId);
            $clinicName.text(job.clinicTitle);
            // $clinicCity.text(job.city)
            $clinicAddress.text(job.zipCode + " " + job.city);
            if (job.url != undefined) {
                $clinicUrl.show().attr('href', job.url);
            } else {
                $clinicUrl.hide();
            }
            if (job.brochure != undefined) {
                $clinicBrochure.attr('href', job.brochure);
            } else {
                $clinicBrochure.hide();
            }

            if (job.awardImage1 != undefined) {
                $awardDivWrapper.show();
                $awardDivs.first().find('img').attr('src', job.awardImage1).attr('alt', job.awardImage1Alt);

                if (job.awardImage2 != undefined) {
                    $awardDivs.eq(1).show().find('img').attr('src', job.awardImage2).attr('alt', job.awardImage2Alt);
                } else {
                    $awardDivs.eq(1).hide();
                }
            } else {
                $awardDivWrapper.hide();
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