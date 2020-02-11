$(function () {
    var $filterGlobal = $('.js-search-form-input'),
        $filterCity = $('.js-search-city'),
        $filterTypeSelect = $('#js-filter-function'),
        $filterSubject = $('#js-filter-subject'),
        $filterJobId = $('.js-filter-jobid'),
        $filterSurrounding = $('.js-search-surrounding'),
        $filterSearchButton = $('.js-search-button'),
        $filterResetButton = $('.js-reset-filter'),
        $headlinePlural = $('#js-headline-plural'),
        $noJobCountSpan = $('#js-no-jobcount-span'),
        $jobCountSpan = $('#js-jobcount-span'),
        $jobCountArticle = $('#js-count-article'),
        $jobCount = $('#js-job-count'),
        nextGreaterSurroundingList = null;
    var latLon = { lat: 0, lon: 0 };

    var listConfig = {
        valueNames: ['id', 'jobTitle', 'jobTitle2', 'jobType', 'jobSubject', 'jobId', 'subjectTitle', 'subjectTitle2', 'clinicTitle', 'city', 'city2', 'zipCode', 'typeFulltime', 'typeFullParttime', 'typeParttime', 'typeLimited'],
        item: 'js-list-entry-template'
    }

    // Pagination-Config
    if (pagination) {
        listConfig.page = 10;
        listConfig.pagination = {
            outerWindow: 1,
        }
    }

    var list = new List('js-joblist', listConfig, jobs);

    // blende nach pagination / filter leere Elemente aus (Vollzeit / Teilzeit) ...
    list.on('updated', function (list) {
        // Anzahl der Jobs ausgeben
        var count = list.matchingItems.length;
        if (count) {
            $noJobCountSpan.hide();
            $jobCount.text(count);
            $jobCountSpan.show();
            // Stellenangebot(e)
            if (count == 1) {
                $jobCountArticle.text('ist');
                $headlinePlural.hide();
            } else {
                $jobCountArticle.text('sind');
                $headlinePlural.show();
            }
        } else {
            $noJobCountSpan.show();
            $jobCountSpan.hide();
            $headlinePlural.hide();
        }
        // tags ein-/ausblenden
        // title-Tag setzen
        $(list.visibleItems).each(function (i, elem) {
            var $domnode = $(elem.elm);
            $domnode.find('.js-typefulltime, .js-typefullparttime, .js-typeparttime, .js-typelimited').each(function (i, elem) {
                var $elem = $(elem);
                if ($elem.text() == '') {
                    $elem.hide();
                } else {
                    $elem.show();
                }
            })
            $domnode.find('.js-showoverlay').prop('title', elem.values().jobTitle);
        })
    });

    // ...initiales Ausblenden 
    $('.js-typefulltime, .js-typefullparttime, .js-typeparttime, .js-typelimited').each(function (i, elem) {
        var $elem = $(elem);
        if ($elem.text() == '') {
            $elem.hide();
        } else {
            $elem.show();
        }
    });

    /**
     * Liefert Filter-Fkt. für 3 untschdl. Listen:
     * 
     * 1. Hauptliste
     * 2. "nächster Umkreis"-Liste
     * 3. Liste zur Karte (filtert nur nach Ort)
     * 
     * erstellt die Filter für die beiden Listen
     * @param {boolean} primaryList
     * @param {boolean} mapList 
     */
    var createFilterFunction = function (theList, primaryList, mapList) {
        var myList = theList;

        function _filterGlobal(job) {
            var globalVal = $filterGlobal.val();

            var regExp = new RegExp(globalVal, 'i');
            return (job.jobTitle.search(regExp) != -1 ||
                job.subjectTitle.search(regExp) != -1 ||
                job.jobId.search(regExp) != -1 ||
                job.city.search(regExp) != -1 ||
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
            // console.log(dist);
            return (dist <= filterVal);
        }

        function _filterByNextGreaterSurrounding(job) {
            var filterVal = parseInt($filterSurrounding.val());
            var nextRadius = parseInt($filterSurrounding.find('option:selected').next().val());

            if (nextRadius == 0) {
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
            if (dist <= filterVal) {
                return false; // passt schon in akt. Filterval, also nicht in erw. Liste anzeigen
            }

            if (dist <= nextRadius) {
                return true;
            }
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
            // Filter nach Voll-/Teilzeit
            var $filterElem = $("input[name='job_kind[]']:checked");
            if ($filterElem.length == 0) {
                return true;
            }
            var filterVal = $filterElem.val();
            return job[filterVal] != "" || job['typeFullParttime'] != undefined;
        }

        var filter = function (item) {
            var job = item.values();

            if (mapList) {
                return _filterByCityZip(job);
            }

            var ret = _filterGlobal(job)
                && _filterByCityZip(job)
                && _filterByFunction(job)
                && _filterBySubject(job)
                && _filterByJobId(job)
                && _filterByJobKind(job);

            if (primaryList) {
                return ret && _filterBySurrounding(job);
            } else {
                return ret && _filterByNextGreaterSurrounding(job);
            }
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
            myList.filter(); // filter leeren
            myList.filter(filter);
        }
    };

    // Pagination: onclick --> hochscrollen
    $(document).on('click', '.page', function (e) {
        $('html, body').animate({ scrollTop: $jobCountSpan.offset().top }, 400);
    })

    if ($filterGlobal.length) {
        var filterList = createFilterFunction(list, true);
        filterList();// initial aufrufen, um Werte zu füllen
        var nextGreaterSurroundingFilterList = null;// wird später definiert

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
            var $this = $(this);
            $('.js-hide-options').find('.active').removeClass('active')
            e.preventDefault();
            $this.addClass('active');
            $filterTypeSelect.val($this.data('id'));
            $filterSearchButton.trigger('click');
        })

        $('.js-search-city').on('input', function () {
            var zip = $(this).val();
            if (zip.length < 5) {
                return;
            }
            // frage https://nominatim.openstreetmap.org/search/?q=Germany,18146&format=json an
            var location = 'Germany,' + zip;
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
            });
        });


        $filterSearchButton.on('click', function (e) {
            e.preventDefault();
            filterList();

            // additional Liste
            if (parseInt($filterSurrounding.val())) {
                $('#js-next-greater-surrounding-joblist').show();
                if (nextGreaterSurroundingList == null) {
                    nextGreaterSurroundingList = new List('js-next-greater-surrounding-joblist', {
                        valueNames: ['id', 'jobTitle', 'jobTitle2', 'jobType', 'jobSubject', 'jobId', 'subjectTitle', 'subjectTitle2', 'clinicTitle', 'city', 'city2', 'zipCode', 'typeFulltime', 'typeParttime', 'typeLimited'],
                        item: 'js-list-entry-template'
                    }, jobs);
                    nextGreaterSurroundingFilterList = createFilterFunction(nextGreaterSurroundingList, false);
                }
                nextGreaterSurroundingFilterList();
                $('#js-next-greater-surrounding-count').text(nextGreaterSurroundingList.matchingItems.length);
                $('#js-next-greater-surrounding-radius').text($filterSurrounding.find('option:selected').next().val())
            }
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

            $('#js-next-greater-surrounding-joblist').hide();

        });
    }

    // click auf Liste --> Anzeige details
    // $('.js-resultlist__item').on('click', (function () {
    $('.js-joblist').on('click', '.js-resultlist__item', (function () {
        var $overlay = $('#overlay'),
            $closeButton = $('.js-close-overlay'),
            $subjectLogo = $('#js-subjectlogo'),
            $jobTitle = $('#js-jobtitle'),
            $subjectTitle = $('#js-subjecttitle'),
            $city = $('#js-city'),
            $logoWrapper = $('#js-cliniclogo-wrapper'),
            $clinicLogo = $('#js-cliniclogo'),
            // $logo = $('js-cliniclogo'),
            $jobName = $('#js-jobname'),
            $jobDescription = $('#js-jobdescription'),
            $youOffer = $('#js-youoffer'),
            $weOffer = $('#js-weoffer'),
            $jobId = $('#js-jobid'),
            $clinicName = $('#clinicName'),
            $clinicAddress = $('#clinicAddress'),
            $clinicStreet = $('#clinicStreet'),
            $clinicUrl = $('#clinicUrl'),
            $clinicBrochure = $('#clinicBrochure'),
            $awardDivWrapper = $('#js-award-div-wrapper'),
            $awardDivs = $('.js-award-div'),
            $clinicContactName = $('#clinicContactName'),
            $clinicContactDepartment = $('#clinicContactDepartment'),
            $clinicContactPhone = $('#clinicContactPhone'),
            $clinicContactMail = $('#clinicContactMail'),
            $jobMailto = $('.jobMailto'),
            $jobEquality = $('#jobEquality'),
            $jobDetailsLink = $('#jobDetailsLink'),
            $clinicDetailHeadline = $('#js-clinic-details-headline'),
            $rehaDetailHeadline = $('#js-reha-details-headline');

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
                'z-index': 1000,
                'opacity': 1,
                scrollTop: 0
            });
        }

        function setJobDetails(job) {
            // setze ein bild, falls vorhanden
            if (subjectImages[job.subjectId] != undefined) {
                var images = subjectImages[job.subjectId];
                var index = Math.floor(Math.random() * images.length)
                $subjectLogo.prop('src', images[index].path)
                    .prop('alt', images[index].alt);
            }
            $jobName.text(job.jobTitle + " - " + job.subjectTitle);
            $jobTitle.text(job.jobTitle);
            $city.text(job.city);
            $.each({
                'typeFulltime': '#js-typefulltime',
                'typeFullParttime': '#js-typefullparttime',
                'typeParttime': '#js-typeparttime',
                'typeLimited': '#js-typelimited'
            }, function (i, sel) {
                // checke gegen Inhalt/Länge
                if (job[i] && job[i].length) {
                    $(sel).show();
                } else {
                    $(sel).hide();
                }
            })
            $subjectTitle.text(job.subjectTitle);
            $jobDescription.html(job.applicationNotes);
            if (job.clinicLogo != undefined) {
                $logoWrapper.show();
                $clinicLogo.attr('src', job.clinicLogo).attr('alt', 'clinicLogoAlt');
            } else {
                $clinicLogoWrapper.hide();
            }
            $youOffer.html(job.youOffer);
            $weOffer.html(job.weOffer);
            $jobId.text(job.jobId);
            if (job.jobId.match(/REHA/)) {
                $rehaDetailHeadline.show();
                $clinicDetailHeadline.hide();
            } else {
                $rehaDetailHeadline.hide();
                $clinicDetailHeadline.show();
            }
            $clinicName.text(job.clinicTitle);
            // $clinicCity.text(job.city)
            $clinicAddress.text(job.zipCode + " " + job.city);
            $clinicStreet.text(job.street + " " + job.houseNumber)

            // console.log($clinicStreet);
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
                $awardDivs.first().show().find('img').attr('src', job.awardImage1).attr('alt', job.awardImage1Alt);

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
            // console.log($jobEquality.length)

            try {
                if (detailUrl != undefined) {
                    $jobDetailsLink.show().prop('href', detailUrl + job.jobAlias + '.html');
                }
            } catch (e) { }
        }
    })());

})