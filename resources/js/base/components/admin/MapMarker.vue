<template>
    <div>
        <gmap-map
            @click="setPlace"
            :center="marker"
            :zoom="18"
            style="width:100%;  height: 400px;">
            <gmap-marker
                :clickable="true"
                :position="marker"
                :draggable="false"
                @drag="updateCoordinates"
            ></gmap-marker>
        </gmap-map>
    </div>
</template>

<script>
import { gmapApi } from 'vue2-google-maps'

export default {
    props: [
        'updateData',
        'moduleName',
        'fieldKey',
        'item'
    ],
    data () {
        return {
            marker: {lat: 41.706706000000025, lng: 44.78735399999999},
            form: {}
        }
    },
    created() {
        if (this.item && this.item.coordinates && this.item.coordinates.lat && this.item.coordinates.lng) {
            this.marker = {
                lat: parseFloat(this.item.coordinates.lat),
                lng: parseFloat(this.item.coordinates.lng),
            }
        }
        this.form = this.item;
    },
    methods: {
        setPlace (place) {
            this.marker = {
                lat: place.latLng.lat(),
                lng: place.latLng.lng()
            }
            this.form.coordinates = this.marker;
            this.updateData(this.moduleName, this.fieldKey,this.form);
        },
        updateCoordinates (location) {
            this.marker = {
                lat: location.latLng.lat(),
                lng: location.latLng.lng()
            }
            this.form.coordinates = this.marker;
            this.updateData(this.moduleName, this.fieldKey,this.form);
        },
    }
}
</script>
