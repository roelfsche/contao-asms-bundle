$(function () {
    var jobDetailDiv = document.getElementById('jobdetails'),
        jobCity = document.getElementById('clinicCity'),
        clinicImg = document.getElementById('clinicImage'),
        clinicName = document.getElementById('clinicName'),
        clinicAddress = document.getElementById('clinicAddress'),
        clinicBrochure = document.getElementById('clinicBrochure'),
        clinicUrl = document.getElementById('clinicUrl'),
        listAnchor = document.getElementById('js-city-joblist');
    var activeJob = [];

    var map = L.map('jobmap').setView([51.133481, 10.018343], 6);
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

    jobs.forEach(function (job, index) {
        L.marker([job.lat, job.lon]).addTo(map).on('click', function () {
            click(job)
        });
    });

    listAnchor.onclick = function (e) {
        e.preventDefault();
        // console.log(activeJob.city)
        var url = e.target.getAttribute('href') + '?city=' + activeJob.city;
        window.location.href = url;
    }

})