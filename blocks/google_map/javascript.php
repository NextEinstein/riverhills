jQuery(document).ready(function () {
    jQuery('#add-marker-link').click(function () {
        alert('oops, not developed yet. Please tell Matt Meisberger');
    });

    jQuery('a.delete-poly-points').click(function () {
        jQuery(this).closest('tr').remove();
    });

    jQuery('a.select-poly-points').click(function () {
        var receiver = jQuery(this).siblings('input');

        receiver.val('');

        var settings = new Object();

        settings.zoom = jQuery('#zoom').val();
        settings.mapcenter = jQuery('input#center').val();
        settings.markers = [];
        settings.mapCanvasId = 'dialog';

        googlemap.initialize(settings);
        googlemap.initPolygonBuilder(receiver);

        $('#dialog').dialog({
            autoOpen: true,
            title: "Polygon Point Chooser",
            width: "900",
            height: "500",
            modal: true
        });
        
        $('#dialog').dialog('open');

    });
});



    var googlemap = (function() {
            var map;
            var geocoder;
            var infowindow;
            var marker;

            /**
              * This function will initialize the map
              *
              * var string  settings.mapcenter   = street address
              * var integer settings.zoom        = zoom level (larger is closer)
              * var string  settings.mapCanvasId = element id of where to put map
              * var json    settings.markers     = address => html for bubble
            */
            var initialize = function (settings) {
                geocoder = new google.maps.Geocoder();

                var latlng = new google.maps.LatLng(-34.397, 150.644);  // until I figure out how to geocode instantly I need to set-up the map first I think

                var myOptions = {
                    zoom: parseInt(settings.zoom),
                    center: latlng,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };

                map = new google.maps.Map(document.getElementById(settings.mapCanvasId),
                    myOptions);

                setCenter(settings.mapcenter);

                for (address in settings.markers) {
                    if (empty(address)) {
                        // nothing to do if no address
                    } else if (empty(settings.markers[address])) {
                        marker = setMarker(address);
                    } else {
                        infowindow = new google.maps.InfoWindow({
                            content: '<div class="google-bubble">' + settings.markers[address] + '</div>'
                        });

                        setMarker(address, infowindow); // modifies the global marker
                    }
                }

                drawPolygons(settings);
            }

            var initPolygonBuilder = function(receiver) {
                google.maps.event.addListener(map, 'click', function(event) {
                    placeMarkerLatlng(event.latLng);

                    jQuery(receiver).val(jQuery(receiver).val() + event.latLng.Ia + ',' + event.latLng.Ja + ';');
                });
            }

            var drawPolygons = function(settings) {
                for (number in settings.polygons) {
                    polygon = settings.polygons[number];

                    if (!empty(polygon) && !empty(polygon.latlng) && !empty(polygon.hex)) {
                    
                        var polycords = [];
                        coordinates = polygon.latlng.split(";");
                        for (key in coordinates) {
                            coordinate = coordinates[key];

                            latlng = coordinate.split(',');

                            if (!empty(latlng[0]) && !empty(latlng[1])) {
                                polycords.push(new google.maps.LatLng(latlng[0], latlng[1]));
                            }
                        }

                        // now that we have all the polygon coordinate translated lets print it to the map
                        var mypolygon = new google.maps.Polygon({
                            paths: polycords,
                            strokeColor : polygon.hex,
                            strokeOpacity: polygon.strokeopacity,
                            strokeWeight: 1,
                            fillColor : polygon.hex,
                            fillOpacity: polygon.fillopacity
                        });

                        mypolygon.setMap(map);

                        //var polygonInfoWindow = new google.maps.InfoWindow();
                        //google.maps.event.addListener(mypolygon, 'click', function() {polygonInfoWindow.setContent('someContent'); polygonInfoWindow.open(map); });
                }
            }
        }

        /* this function is almost strait from google */
        var setCenter = function(address) {
            geocoder.geocode( { 'address': address }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    map.setCenter(results[0].geometry.location);
                } else {
                    alert("Geocode was not successful for the following reason: " + status);
                }
            });
        }

        var placeMarkerLatlng = function(location) {
            var marker = new google.maps.Marker({
                position: location,
                map: map
            });
        }

        var setMarker = function(address, infowindow) {
            geocoder.geocode( { 'address': address }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    marker = new google.maps.Marker({
                                    map: map,
                                    position: results[0].geometry.location
                    });

                    if (!empty(infowindow)) {
                        google.maps.event.addListener(marker, 'click', function() {
                            infowindow.open(map,marker);
                        });
                    }
                } else {
                    alert("Geocode was not successful for the following reason: " + status);
                }
            });
        }

        return {
            initialize: function(settings) {initialize(settings);},
            initPolygonBuilder: function(listnerid) {initPolygonBuilder(listnerid);}
        }
    })();

    function empty (mixed_var) {
        // !No description available for empty. @php.js developers: Please update the function summary text file.
        //
        // version: 1009.2513
        // discuss at: http://phpjs.org/functions/empty

        var key;

        if (mixed_var === "" ||
            mixed_var === 0 ||
            mixed_var === "0" ||
            mixed_var === null ||
            mixed_var === false ||
            typeof mixed_var === 'undefined'
        ){
            return true;
        }

        if (typeof mixed_var == 'object') {
            for (key in mixed_var) {
                return false;
            }
            return true;
        }

        return false;
    }
