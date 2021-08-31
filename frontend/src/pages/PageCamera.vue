<template>
  <q-page class="constrain-more q-pa-md">

    <div class="camera-frame q-pa-md">
      <video v-show="!imageCaptured" ref="video" class="full-width" autoplay />
      <canvas v-show="imageCaptured" ref="canvas" class="full-width" height="240"></canvas>
    </div>

    <div class="text-center q-pa-md">

      <q-btn v-if="hasCameraSupport" @click="captureImage" color="grey-10" icon="eva-camera" size="lg" round />
      <q-file v-else v-model="imageUpload" @input="captureImageFallback" label="Chose a image" outlined accept="image/*">
        <template v-slot:prepend>
          <q-icon name="eva-attach-outline" />
        </template>
      </q-file>

      <div class="row justify-center q-ma-md">
        <q-input v-model="post.caption" class="col col-sm-6" label="Caption" dense />
      </div>

      <div class="row justify-center q-ma-md">
        <q-input v-model="post.location" :loading="locationLoading" class="col col-sm-6" label="Location" dense>
          <template v-slot:append>
            <q-btn v-if="!locationLoading && locationSupported" round dense flat icon="eva-navigation-2-outline" @click="getLocation" />
          </template>
        </q-input>
      </div>

      <div class="row justify-center q-mt-lg">     
        <q-btn @click="addPost" unelevated rounded color="primary" label="Post Image" /> 
      </div>

    </div>   

  </q-page>
</template>

<script>
import { defineComponent } from 'vue'
import { uid } from 'quasar' // Gerador de ID's únicos
require('md-gum-polyfill') // Instalamos a biblioteca Poliyfill (npm install --save md-gum-polyfill)

// Web Api de localização https://geocode.xyz/

export default defineComponent({
  name: 'PageCamera',
  data () {
    return {
      post: {
        id: uid(),
        caption: '',
        location: '',
        photo: null,
        date: Date.now()
      },
      imageCaptured: false,
      imageUpload: [],
      hasCameraSupport: true,
      locationLoading: false
    }
  },
  computed: {
    locationSupported () {
      // Verificando se o navegagor possui suporte a geolocalização
      if ('geolocation' in navigator) return true
      else return false
    }
  },
  methods: {
    initCamera () {
      // Chamando o vídeo da câmera (esse método nativo é uma promisse)
      navigator.mediaDevices.getUserMedia({
        video: true
      }).then(stream => {
        // Chamando o ref 'video' que demos a tag 'video'
        this.$refs.video.srcObject = stream
      }).catch(error => {
        // Entrará aqui no caso do usuário não tiver acesso a câmera (marcar aquele "não permitir", por exemplo)
        this.hasCameraSupport = false
      })
    },
    captureImage () {
      let video = this.$refs.video
      let canvas = this.$refs.canvas
      canvas.width = video.getBoundingClientRect().width // Dimensionando a largura do canvas de acordo com o video
      canvas.height = video.getBoundingClientRect().height // Dimensionando a altura do canvas de acordo com o video
      let context = canvas.getContext('2d')
      context.drawImage(video, 0, 0, canvas.width, canvas.height)
      this.imageCaptured = true
      this.post.photo = this.dataURItoBlob(canvas.toDataURL()) // Pegando os dados de base64
      
      this.disableCamera() // desativando a Câmera
    },
    captureImageFallback (file) {
      // this.post.photo = file
      this.post.photo = file.target.files[0]   
      //console.log(file.target.files[0])

      let canvas = this.$refs.canvas    
      let context = canvas.getContext('2d')
      
      // Essa parte abaixo foi pega no stackoverflow (pelo professor)
      // https://stackoverflow.com/questions/10906734/how-to-upload-image-into-html5-canvas
      var reader = new FileReader()
      reader.onload = event => {
        var img = new Image()
        img.onload = () => {
          canvas.width = img.width
          canvas.height = img.height
          context.drawImage(img,0,0)

          this.imageCaptured = true
        }
        img.src = event.target.result
      }
      // reader.readAsDataURL(file)
      reader.readAsDataURL(file.target.files[0])

    },
    disableCamera () {
      // desativando a Câmera
      this.$refs.video.srcObject.getVideoTracks().forEach(track => {
        track.stop()
      })
    },
    dataURItoBlob(dataURI) {
      // ESSE MÉTODO FOI PEGO NO STACKOVERFLOW (INCLUSIVE O PROFESSOR QUE PEGOU)
      // https://stackoverflow.com/questions/12168909/blob-from-dataurl

      // convert base64 to raw binary data held in a string
      // doesn't handle URLEncoded DataURIs - see SO answer #6850276 for code that does this
      var byteString = atob(dataURI.split(',')[1]);

      // separate out the mime component
      var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0]

      // write the bytes of the string to an ArrayBuffer
      var ab = new ArrayBuffer(byteString.length);

      // create a view into the buffer
      var ia = new Uint8Array(ab);

      // set the bytes of the buffer to the correct values
      for (var i = 0; i < byteString.length; i++) {
          ia[i] = byteString.charCodeAt(i);
      }

      // write the ArrayBuffer to a blob, and you're done
      var blob = new Blob([ab], {type: mimeString});
      return blob;

    },
    getLocation () {
      this.locationLoading = true
      navigator.geolocation.getCurrentPosition(position => {
        this.getCityAndCountry(position)
      }, err => {
        console.log("Error: ", err)
      }, { timeout: 7000 }) // Adicionamos um tempo para ser pega a localização
    },
    getCityAndCountry (position) {      
      let apiUrl = `https://geocode.xyz/${ position.coords.latitude },${ position.coords.longitude }?json=1`
      //let apiUrl = `http://maps.googleapis.com/maps/api/staticmap?center=+${ position.coords.latitude },${ position.coords.longitude }+&zoom=14&size=400x300&sensor=false`
      this.$axios.get(apiUrl).then(result => {
        this.locationSuccess(result)
      }).catch(err => {
        console.log("Err: ", err)
        this.locationError()
      })
    },
    locationSuccess (result) {
      this.post.location = result.data.city
      if (result.data.country) {
        this.post.location += `, ${ result.data.country }`
      }
      this.locationLoading = false
    },
    locationError(){
      this.$q.dialog({
        title: 'Error',
        message: 'Could not find your location.'
      })
      this.locationLoading = false
    },
    addPost () {
      let formData = new FormData()
      //formData.append('id', this.post.id)
      formData.append('caption', this.post.caption)
      formData.append('location', this.post.location)
      //formData.append('date', this.post.date)
      formData.append('image', this.post.photo)

      axios.post(`${ process.env.API }/posts`, formData) // Fazendo a requisição no API
        .then((resp) => {
          console.log(resp.data.posts)        
          this.posts = resp.data.posts
          this.$q.loading.hide() // Encerrando o loading
        })
        .catch((err) => {
          // Chamando o plugin de notificação
          this.$q.notify({
            position: 'center', // Posição que a mensagem aparecerá
            timeout: 3000, // Tempo de exposição da mensagem no browser (3000 = 3 segundos)
            color: 'negative', // Cor do background da mensagem
            textColor: 'white', // Cor do texto da mensagem
            actions: [{ icon: 'check', color: 'white' }], // Ícone e cor do ícone
            message: 'Erro na requisição. Erro: ' + err // Texto da mensagem
          })
          this.$q.loading.hide() // Encerrando o loading
        })
    }
  },  
  mounted () {
    this.initCamera()  
  },
  beforeDestroy () {
    if (this.hasCameraSupport) {
      this.disableCamera() // desativando a Câmera
    }
  }
})
</script>

<style lang="sass">
.camera-frame
  border: 2px solid $grey-10
  border-radius: 10px
</style>
