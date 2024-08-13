<script setup>
import { useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import 'leaflet/dist/leaflet.css';
import { onMounted, ref, watch } from 'vue';
import L from 'leaflet';

const props = defineProps({
  errors: Object,
  bathrooms: Object, //or just bathroom if you want the current,
  duplicate: Boolean,
  existingEntry: Object
});

let showPopup = ref(false);

const form = useForm({
  name: null, // can do props.bathroom.name
  location: null,
  code: null,
  note: null,
});

let updateForm = useForm({
  name: '',//props.existingEntry?.name ?? null,
  location: '',//props.existingEntry?.location ?? null,
  code: '',//props.existingEntry?.code ?? null,
  note: ''//props.existingEntry?.note ?? null,
});

function submit() {
  form.post('/', {
    preserveState: true,
    preserveScroll: true,
    onSuccess: (page) => {
      if (page.props.duplicate) {
        showPopup.value = true;
        updateForm.name = page.props.existingEntry.name;
        updateForm.location = page.props.existingEntry.location;
        updateForm.code = page.props.existingEntry.code;
        updateForm.note = page.props.existingEntry.note;
      } else {
        //Reset form if successfully created
        resetForm();
      }
    }
  });
}

function updateEntry() {
  if (props.existingEntry) {
    updateForm.put(`/${props.existingEntry.id}`, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        showPopup.value = false;
        resetForm(); // This will reset both forms
      }
    });
  }
}

function resetForm() {

  // Manually reset form fields because being annoying
  form.name = null;
  form.location = null;
  form.code = null;
  form.note = null;
  form.clearErrors();

  updateForm.reset();
}

function cancelUpdate() {
  showPopup.value = false;
  updateForm.reset();
}


const map = ref(null);
const markers = ref([]);
const selectedBathroom = ref(null);
const userMarker = ref(null);

function addMarkers() {
  markers.value.forEach(marker => map.value.removeLayer(marker));
  markers.value = [];

  if ("geolocation" in navigator) {
    userMarker.value = ref(null);

    if (userMarker.value) {
      markers.value.push(userMarker.value);
    }

    navigator.geolocation.getCurrentPosition(function(position) {
      const userLat = position.coords.latitude;
      const userLng = position.coords.longitude;
      
      userMarker.value = L.marker([userLat, userLng], {
        icon: L.icon({
          iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
          shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
          iconSize: [25, 41],
          iconAnchor: [12, 41],
          popupAnchor: [1, -34],
          shadowSize: [41, 41]
        })
      }).addTo(map.value);
      
      userMarker.value.bindPopup("You are here");

      map.value.setView([userLat, userLng], 15);
      
      // Refresh markers to include user location
      addMarkers();
    }, function(error) {
      console.error("Error getting user location:", error);
    });
  } else {
    console.log("Geolocation is not supported by this browser.");
  }

  props.bathrooms.forEach(bathroom => {
    if (bathroom.latitude && bathroom.longitude) {
      const marker = L.marker([bathroom.latitude, bathroom.longitude])
        .addTo(map.value)
        .bindPopup(`${bathroom.name}<br>${bathroom.location}<br>${bathroom.code}<br>${bathroom.note}`);
      
      marker.on('click', () => {
        selectedBathroom.value = bathroom;
      });

      markers.value.push(marker);
    }
  });
  if (markers.value.length > 0) {
    const group = L.featureGroup(markers.value);
    map.value.fitBounds(group.getBounds().pad(0.1));
  }
}

onMounted(() => {
  map.value = L.map('map');//.setView([0, 0], 2);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map.value);

  // Add markers after the map is initialized
  addMarkers();

});

// Update the watch function to use the new addMarkers function
watch(() => props.bathrooms, () => {
  addMarkers();
}, { deep: true });


function getDirections() {
  if (selectedBathroom.value && selectedBathroom.value.latitude && selectedBathroom.value.longitude) {
    const { latitude, longitude } = selectedBathroom.value;
    const address = encodeURIComponent(selectedBathroom.value.location);

    // Check if the user is on iOS or macOS
    const isApple = /iPad|iPhone|iPod|Macintosh/.test(navigator.userAgent) && !window.MSStream;
    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

    let url;
    if (isApple && isMobile) {
      // Use Apple Maps on iOS devices
      url = `maps://maps.apple.com/?daddr=${latitude},${longitude}`;
    } else if (isApple && !isMobile) {
      // Use Apple Maps on macOS
      url = `http://maps.apple.com/?daddr=${latitude},${longitude}`;
    } else {
      // Use Google Maps for other devices
      url = `https://www.google.com/maps/dir/?api=1&destination=${latitude},${longitude}&destination_place_id=${address}`;
    }

    window.open(url, '_blank');
  } else {
    console.error('No bathroom selected or coordinates not available');
  }
}

</script>

<template>

  <div class="container mx-auto p-10">
    <div class="flex flex-col md:flex-row md:space-x-4">
      <div class="w-full md:w-1/3 mb-4 md:mb-0">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden h-full">
          <img class="w-full h-48 object-cover" src="https://images2.minutemediacdn.com/image/upload/c_crop,w_2121,h_1193,x_0,y_77/c_fill,w_1440,ar_16:9,f_auto,q_auto,g_auto/images/voltaxMediaLibrary/mmsport/mentalfloss/01g4kfw8ppqvd45pjfaa.jpg" alt="Toilet">
          <div class="p-6 flex flex-col h-[calc(100%-12rem)]">
            <h1 class="text-2xl font-bold mb-2 mx-auto">Welcome to CrapMap</h1>
            <p class="text-gray-700 mb-4 mx-auto">The destination to spare your pants.</p>
            <form @submit.prevent="submit" class="space-y-4 flex-grow flex flex-col justify-between">
              <div class="space-y-4">
                <div>
                  <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Bathroom Name</label>
                  <input class="w-full px-3 py-2 border rounded-lg" id="name" v-model="form.name" type="text" placeholder="Enter name">
                </div>
                <div>
                  <label class="block text-gray-700 text-sm font-bold mb-2" for="location">Location</label>
                  <input class="w-full px-3 py-2 border rounded-lg" id="location" v-model="form.location" type="text" placeholder="Enter location (# Street City State)">
                </div>
                <div>
                  <label class="block text-gray-700 text-sm font-bold mb-2" for="code">Code</label>
                  <input class="w-full px-3 py-2 border rounded-lg" id="code" v-model="form.code" type="number" placeholder="Enter code">
                </div>
                <div>
                  <label class="block text-gray-700 text-sm font-bold mb-2" for="note">Notes</label>
                  <input class="w-full px-3 py-2 border rounded-lg" id="note" v-model="form.note" type="text" placeholder="Enter notes">
                </div>
              </div>
              <button class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg" type="submit">Submit</button>
            </form>
          </div>
        </div>
      </div>

  <div class="w-full md:w-2/3">
        <div id="map" class="md:h-2/3 rounded-lg shadow-lg mb-4 z-10"></div>

        <div v-if="$page.props.flash.message" class="relative px-3 py-3 mb-4 border rounded bg-green-200 border-green-300 text-green-800">
          <strong>{{ $page.props.flash.message }}</strong>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg border-t border-b p-4 md:h-1/8">
          <table v-if="selectedBathroom" class="w-full">
            <thead>
              <tr>
                <th class="text-left">Name</th>
                <th class="text-left">Location</th>
                <th class="text-left">Code</th>
                <th class="text-left">Notes</th>
                <th class="text-center">Need to go?</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="text-left pt-2 whitespace-normal break-words max-w-xs">{{ selectedBathroom.name }}</td>
                <td class="text-left pt-2 whitespace-normal break-words max-w-xs">{{ selectedBathroom.location }}</td>
                <td class="text-left pt-2">{{ selectedBathroom.code }}</td>
                <td class="text-left pt-2 whitespace-normal break-words max-w-xs">{{ selectedBathroom.note }}</td>
                <td class="text-center pt-2">
                  <button @click.prevent="getDirections" class="bg-teal-500 hover:bg-teal-700 text-white font-bold py-1 px-3 rounded">
                    Directions
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <p v-else class="text-center text-gray-600">Click on a map marker to view bathroom details.</p>
        </div>

        <div class="bg-gray rounded-lg shadow-lg border-t border-b p-4 md:h-1/6">
          <table class="w-full">
            <thead>
              <tr>
                <th class="text-left">How to Use</th>
              </tr>
            </thead>
            <tbody>
              <tr class="text-center">
                <td class="pt-2">
                  <img class="inline" src="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png">
                  <span class="px-2">You are here</span>
                </td>
                <td class="pt-2">
                  <img class="inline" src="https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png">
                  <span class="px-2">Bathrooms are here</span>
                </td>
                <td class="pt-1">
                  <span class="px-2">Click on the map to get <b>codes</b> and <b>directions</b></span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

<div v-if="showPopup" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
  <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
    <h3 class="text-lg font-bold mb-2">Update Existing Bathroom</h3>
    <p class="pb-3">A bathroom at this location already exists. Would you like to update it?</p>
    <form @submit.prevent="updateEntry">
      <input v-model="updateForm.name" type="text" placeholder="Name" readonly class="w-full mb-2 p-2 border rounded">
      <input v-model="updateForm.location" type="text" placeholder="Location" readonly class="w-full mb-2 p-2 border rounded">
      <input v-model="updateForm.code" type="number" placeholder="New Code" class="w-full mb-2 p-2 border rounded">
      <input v-model="updateForm.note" type="text" placeholder="New Note" class="w-full mb-2 p-2 border rounded">
      <div class="flex justify-end space-x-2">
          <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
          <button @click="cancelUpdate" type="button" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
      </div>
    </form>
  </div>
</div>

</template>
