$(function () {
    var jobDetailDiv = document.getElementById('jobdetails'),
        jobMapDetailOverlayClose = document.getElementById('jsOverlayClose'),
        jobCity = document.getElementById('js-mapClinicCity'),
        clinicImg = document.getElementById('js-mapClinicImage'),
        clinicName = document.getElementById('js-mapClinicName'),
        clinicAddress = document.getElementById('js-mapClinicAddress'),
        clinicStreet = document.getElementById('js-mapClinicStreet'),
        clinicBrochure = document.getElementById('js-mapClinicBrochure'),
        clinicUrl = document.getElementById('js-mapClinicUrl'),
        listAnchor = document.getElementById('js-city-joblist'),
        noJobSpan = document.getElementById('js-no-joblist');
    var activeJob = [];
    var $searchFilter = $('.js-search-filter'),
        $searchResults = $('.js-search-results'),
        $searchCity = $('.js-search-city'),
        $filterButton = $('.js-search-button');
    var icon = null,
        selectedIcon = null,
        emptyIcon = null;
    var selectedMarker = null;

    var map = L.map('jobmap', {
        minZoom: 6,
        maxZoom: 10
    }).setView([51.133481, 10.018343], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var click = function (job, marker) {
        // console.log('click' + marker);
        if (selectedMarker) {
            if (selectedMarker.oldIcon) {
                selectedMarker.setIcon(selectedMarker.oldIcon);
            }
        }

        activeJob = job;
        marker.setIcon(selectedIcon);
        selectedMarker = marker;
        jobDetailDiv.style.display = 'block';
        if (job.id == null) {
            listAnchor.style.display = 'none';
            noJobSpan.style.display = 'inline';
        } else {
            listAnchor.style.display = 'block';
            noJobSpan.style.display = 'none';
        }
        jobCity.innerHTML = job.city;
        if (job.optionalImage != undefined) {
            clinicImg.setAttribute('src', job.optionalImage);
            clinicImg.setAttribute('alt', job.optionalImageAlt);
        }
        clinicName.innerHTML = job.clinicTitle;
        clinicAddress.innerHTML = job.zipCode + " " + job.city;
        clinicStreet.innerHTML = job.street + " " + job.houseNumber;

        if (job.url != undefined) {
            clinicUrl.setAttribute('href', job.url);
            clinicUrl.style.display = 'block';
        } else {
            clinicUrl.style.display = 'none';
        }

        if (job.brochure != undefined) {
            clinicBrochure.setAttribute('href', job.brochure);
            clinicBrochure.style.display = 'block';
        } else {
            clinicBrochure.style.display = 'none';
        }
    }

    // Marker
    icon = new L.Icon({
        iconUrl: 'bundles/contaoasms/css/images/marker-icon.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
    emptyIcon = new L.Icon({
        iconUrl: 'bundles/contaoasms/css/images/marker-icon-alt.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
    selectedIcon = new L.Icon({
        iconUrl: 'bundles/contaoasms/css/images/marker-icon-select.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    var _marker = null;
    mapJobs.forEach(function (job, index) {
        _marker = L.marker([job.lat, job.lon]).addTo(map).on('click', function () {
            click(job, this)
        });
        if (job.id == null) {
            _marker.setIcon(emptyIcon);
            _marker.oldIcon = emptyIcon;
        } else {
            _marker.setIcon(icon);
            _marker.oldIcon = icon;
        }
    });

    // passe die liste an, falls vorhanen
    if ($searchFilter.length) {
        $searchFilter.hide();
        $searchResults.hide();
    }

    listAnchor.onclick = function (e) {
        e.preventDefault();
        // map-overlay wieder schliessen
        jobDetailDiv.style.display = 'none';
        
        // var url = e.target.getAttribute('href') + '?city=' + activeJob.city;
        // window.location.href = url;
        $searchCity.val(activeJob.city);
        $filterButton.trigger('click');
        $searchResults.show();
        $([document.documentElement, document.body]).animate({'scrollTop': $searchResults.offset().top}, 800);
    }

    jobMapDetailOverlayClose.onclick = function (e) {
        $(jobDetailDiv).hide();
    }

})