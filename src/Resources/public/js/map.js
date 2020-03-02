$(function () {
    var jobDetailDiv = document.getElementById('jobdetails'),
        jobMapDetailOverlayClose = document.getElementById('jsOverlayClose'),
        jobCity = document.getElementById('clinicCity'),
        clinicImg = document.getElementById('clinicImage'),
        clinicName = document.getElementById('clinicName'),
        clinicAddress = document.getElementById('clinicAddress'),
        clinicStreet = document.getElementById('clinicStreet'),
        clinicBrochure = document.getElementById('clinicBrochure'),
        clinicUrl = document.getElementById('clinicUrl'),
        listAnchor = document.getElementById('js-city-joblist');
    var activeJob = [];
    var $searchFilter = $('.js-search-filter'),
        $searchResults = $('.js-search-results'),
        $searchCity = $('.js-search-city'),
        $filterButton = $('.js-search-button');

    var map = L.map('jobmap', {
        minZoom: 6,
        maxZoom: 10
    }).setView([51.133481, 10.018343], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var click = function (job) {
        // console.log('click' + job.id);
        activeJob = job;
        jobDetailDiv.style.display = 'block';
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
            clinicUrl.style.display = 'hide';
        }

        if (job.brochure != undefined) {
            clinicBrochure.setAttribute('href', job.brochure);
            clinicBrochure.style.display = 'block';
        } else {
            clinicBrochure.style.display = 'hide';
        }
    }

    mapJobs.forEach(function (job, index) {
        L.marker([job.lat, job.lon]).addTo(map).on('click', function () {
            click(job)
        });
    });

    // passe die liste an, falls vorhanen
    if ($searchFilter.length) {
        $searchFilter.hide();
        $searchResults.hide();
    }

    listAnchor.onclick = function (e) {
        e.preventDefault();
        // var url = e.target.getAttribute('href') + '?city=' + activeJob.city;
        // window.location.href = url;
        $searchCity.val(activeJob.city);
        $filterButton.trigger('click');
        $searchResults.show();
    }

    jobMapDetailOverlayClose.onclick = function (e) {
        $(jobDetailDiv).hide();
    }

})