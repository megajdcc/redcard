$(document).ready(function() {
    'use strict';
    var map ;
    if($('#input-latitude').val() != '' && $('#input-longitude').val() != ''){
        var center = new google.maps.LatLng($('#input-latitude').val(), $('#input-longitude').val());
        var zoom = 17;
    }else{
        var center = new google.maps.LatLng(20.65340699999999, -105.2253316);
        var zoom = 13;
    }

    function initialize() {
        var mapOptions = {
            center: center,
            zoom: zoom,
            scrollwheel: false
        };
        map = new google.maps.Map(document.getElementById('map-canvas'),
        mapOptions);

        var input = /** @type {HTMLInputElement} */(
            document.getElementById('pac-input'));

            var types = document.getElementById('type-selector');

            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(types);

            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo('bounds', map);

            var infowindow = new google.maps.InfoWindow();
            var marker = new google.maps.Marker({
                position: center,
                draggable: true,
                map: map,
                anchorPoint: new google.maps.Point(0, -35)
            });

            google.maps.event.addListener(marker, "mouseup", function(event) {
                $('#input-latitude').val(this.position.lat());
                $('#input-longitude').val(this.position.lng());
                $('#input-latitude').prop('readonly', true);
                $('#input-longitude').prop('readonly', true);
            });

            google.maps.event.addListener(autocomplete, 'place_changed', function() {
                infowindow.close();
                marker.setVisible(false);
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                    return;
                }

                // If the place has a geometry, then present it on a map.
                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);
                }
                marker.setIcon(/** @type {google.maps.Icon} */({
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(35, 35)
                }));
                marker.setPosition(place.geometry.location);
                marker.setVisible(true);

                $('#input-latitude').val(place.geometry.location.lat());
                $('#input-longitude').val(place.geometry.location.lng());

                var address = '';
                if (place.address_components) {
                    address = [
                    (place.address_components[0] && place.address_components[0].short_name || ''),
                    (place.address_components[1] && place.address_components[1].short_name || ''),
                    (place.address_components[2] && place.address_components[2].short_name || '')
                    ].join(' ');
                }

                infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
                infowindow.open(map, marker);
            });
        }

         if ($('#map-canvas').length != 0) {
            google.maps.event.addDomListener(window, 'load', initialize);
        }
    
       
        
});
