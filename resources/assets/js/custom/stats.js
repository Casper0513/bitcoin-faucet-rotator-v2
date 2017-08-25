
$.fn.dataTable.ext.errMode = 'none';

function generateVisitorsTable(data, elementToRender){

    if(typeof data !== 'undefined' && typeof $(elementToRender) !== 'undefined'){
        return $(elementToRender).DataTable({
            processing: true,
            data: data,
            language: {
                processing: "Loading...",
            },
            order: [[2, "desc"], [3, "desc"], [4, "desc"], [5,"desc"], [6, "desc"], [7, "asc"], [8, "desc"]],
            columns: [
                {data: "url"},
                {data: "pageTitle"},
                {
                    data: "uniqueVisitors",
                    type: 'num',
                    render: {
                        _: 'display',
                        sort: 'original'
                    }
                },
                {
                    data: "pageViews",
                    type: 'num',
                    render: {
                        _: 'display',
                        sort: 'original'
                    }
                },
                {
                    data: "uniquePageViews",
                    type: 'num',
                    render: {
                        _: 'display',
                        sort: 'original'
                    }
                },
                {
                    data: 'aveSessionDuration',
                    type: 'num',
                    render: {
                        _: 'display',
                        sort: 'original'
                    }
                },
                {
                    data: 'aveTimeOnPage',
                    type: 'num',
                    render: {
                        _: 'display',
                        sort: 'original'
                    }
                },
                {
                    data: "noOfBounces",
                    type: 'num',
                    render: {
                        _: 'display',
                        sort: 'original'
                    }
                },
                {data: "noOfCountries"}
            ],
            responsive: true
        });
    }
}

function generateVisitorsLineChart(data, chartElement){

    var visitorsRGB = getRandomRgb();
    var pageViewsRGB = getRandomRgb();

    if(typeof data !== 'undefined' && data.status !== 'error' && typeof $(chartElement)[0] !== 'undefined'){

        var areaChartContext = $(chartElement)[0].getContext('2d');;

        var labels = [], visitorsData = [], pageViewsData = [];

        // Separate data and labels into their own arrays.
        data.forEach(function(d) {
            labels.push(new Date(d.date).formatDDMMYYYY());
            visitorsData.push(parseInt(d.visitors));
            pageViewsData.push(parseInt(d.pageViews));
        });

        var config = {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Visitors",
                        borderColor: rgbaString(visitorsRGB.r, visitorsRGB.g, visitorsRGB.b),
                        backgroundColor: rgbaString(visitorsRGB.r, visitorsRGB.g, visitorsRGB.b, 0.5),
                        data: visitorsData
                    },
                    {
                        label: "Page views",
                        borderColor: rgbaString(pageViewsRGB.r, pageViewsRGB.g, pageViewsRGB.b),
                        backgroundColor: rgbaString(pageViewsRGB.r, pageViewsRGB.g, pageViewsRGB.b, 0.5),
                        data: pageViewsData
                    }
                ]
            },
            options: {
                showScale: true,
                scaleShowGridLines: true,
                scaleGridLineColor: "rgba(0,0,0,.05)",
                scaleGridLineWidth: 1,
                scaleShowHorizontalLines: true,
                scaleShowVerticalLines: true,
                bezierCurve: true,
                bezierCurveTension: 0.8,
                pointDot: false,
                pointDotRadius: 4,
                pointDotStrokeWidth: 1,
                pointHitDetectionRadius: 20,
                datasetStroke: true,
                datasetStrokeWidth: 2,
                datasetFill: true,
                maintainAspectRatio: true,
                responsive: true,
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function (t, d) {
                            if (t.datasetIndex === 0) {
                                return "Visitors: " + t.yLabel.toString();
                            } else if (t.datasetIndex === 1) {
                                return "Page Views: " + t.yLabel.toString();
                            }
                        }
                    }
                },
                scales: {
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Number of Visitors / Page Views'
                        }
                    }],
                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Date'
                        }
                    }]
                }
            }
        };
        return new Chart(areaChartContext, config);
    }
}

function getVisitorsDataAjax(routeName, dateFrom, dateTo, quantity)
{
    if(typeof quantity === 'undefined'){
        quantity = 10;
    }

    var error = {
        status: 'error',
        message: '',
        code: ''
    };

    if(typeof routeName !== 'undefined' && dateFrom !== null && dateTo !== null){

        var visitorsRoute = laroute.route(
            routeName,
            { dateFrom : dateFrom, dateTo: dateTo, quantity: quantity }
        );

        if(typeof visitorsRoute === 'undefined'){
            return null;
        }

        return $.get(visitorsRoute, function(response){
            return response.data;
        }).promise();
    } else {
        error.message = "Both dates cannot be null / empty.";
        error.code = 500;
        visitorsRoute = laroute.route(
            routeName,
            { dateFrom : dateFrom, dateTo: dateTo, quantity: quantity }
        );

        return $.get(visitorsRoute, function(){
            return error;
        }).promise();
    }
}

function getBrowserStatsAjax(dateFrom, dateTo, maxBrowsers){

    if(typeof maxBrowsers === 'undefined'){
        maxBrowsers = 10;
    }

    var error = {
        status: 'error',
        message: '',
        code: ''
    };

    if(dateFrom !== null && dateTo !== null){

        var browsersRoute = laroute.route(
            'stats.top-x-browsers',
            { dateFrom : dateFrom, dateTo: dateTo, maxBrowsers: maxBrowsers }
        );

        return $.get(browsersRoute, function(response){
            return response.data;
        }).promise();
    } else {
        error.message = "Both dates cannot be null / empty.";
        error.code = 500;

        browsersRoute = laroute.route(
            'stats.top-x-browsers',
            { dateFrom : dateFrom, dateTo: dateTo, maxBrowsers: maxBrowsers }
        );

        return $.get(browsersRoute, function(){
            return error;
        }).promise();
    }
}

function generatePieDonutChart(data, renderToElement){
    if(typeof data !== 'undefined' && typeof $(renderToElement) !== 'undefined'){

        var pieChartCanvas;
        if(renderToElement.charAt(0) === '.'){
            pieChartCanvas = document.getElementsByClassName(renderToElement.substr(1))[0];
        } else if (renderToElement.charAt(0) === '#') {
            pieChartCanvas = document.getElementById(renderToElement.substr(1));
        } else {
            console.log("Pie chart element is not correctly defined.");
            return null;
        }

        var pieChartContext = pieChartCanvas.getContext("2d");
        var pieChartOptions = {
            segmentShowStroke: true,
            segmentStrokeColor: "#fff",
            segmentStrokeWidth: 2,
            percentageInnerCutout: 50, // This is 0 for Pie charts
            animationSteps: 100,
            animationEasing: "easeOutBounce",
            animateRotate: true,
            animateScale: false,
            responsive: true,
            maintainAspectRatio: true
        };

        var backgroundColors = [];
        var labels = [];
        var dataItems = [];

        data.forEach(function(d) {
            labels.push(d.browser);
            dataItems.push(parseInt(d.sessions));
            backgroundColors.push(getRandomHexColor());
        });

        return new Chart(
            pieChartContext, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        backgroundColor: backgroundColors,
                        data: dataItems
                    }],
                    options: pieChartOptions
                }
            }
        );
    }
}

function getCountriesAndVisitorsAjax(dateFrom, dateTo)
{

    var error = {
        status: 'error',
        message: '',
        code: ''
    };

    if(dateFrom !== null && dateTo !== null){

        var browsersRoute = laroute.route(
            'stats.countries-and-visitors',
            { dateFrom : dateFrom, dateTo: dateTo }
        );

        return $.get(browsersRoute, function(response){
            return response.data;
        }).promise();
    } else {

        browsersRoute = laroute.route(
            'stats.countries-and-visitors',
            { dateFrom : dateFrom, dateTo: dateTo }
        );

        error.message = "Both dates cannot be null / empty.";
        error.code = 500;

        return $.get(browsersRoute, function(){
            return error;
        }).promise();
    }
}

function generateGoogleGeoChart(data, renderToElement){

    var geoChartCanvas;
    if(renderToElement.charAt(0) === '.'){
        geoChartCanvas = document.getElementsByClassName(renderToElement.substr(1))[0];
    } else if (renderToElement.charAt(0) === '#') {
        geoChartCanvas = document.getElementById(renderToElement.substr(1));
    } else {
        console.log("Pie chart element is not correctly defined.");
        return null;
    }

    google.charts.load('current', {
        'packages': ['geochart'],
        // Note: you will need to get a mapsApiKey for your project.
        // See: https://developers.google.com/chart/interactive/docs/basic_load_libs#load-settings
        'mapsApiKey': 'AIzaSyD-9tSrke72PouQMnMX-a7eZSW0jkFMBWY'
    });

    for (var i = 0; i < data.length; i++) {
        if (!isNaN(data[i][1])) {
            data[i][1] = parseInt(data[i][1]);
        }
    }

    var geoData = data;

    var drawRegionsMap = function(){

        geoChartCanvas.innerHTML = "";
        var data = google.visualization.arrayToDataTable(geoData);

        var options = {};
        options.width = '90%';
        options.showLegend = true;
        options.showZoomOut = true;
        options.zoomOutLabel = 'Zoom Out';

        var chart = new google.visualization.GeoChart(geoChartCanvas);

        chart.draw(data, options);
    };

    google.charts.setOnLoadCallback(drawRegionsMap);

    var windowResizeTimer;

    jQuery(window).on('resize', function () {
        clearTimeout(windowResizeTimer);
        windowResizeTimer = setTimeout(function () {
            drawRegionsMap();
        }, 250);
    });

    jQuery(window).on('reload', function () {
        clearTimeout(windowResizeTimer);
        windowResizeTimer = setTimeout(function () {
            drawRegionsMap();
        }, 250);
    });

}
