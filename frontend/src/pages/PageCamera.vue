<template>
  <q-page class="constrain-more q-pa-md">

    <div class="camera-frame q-pa-md">
      <video v-show="!imageCaptured" ref="video" class="full-width" autoplay />
      <canvas v-show="imageCaptured" ref="canvas" class="full-width" height="240"></canvas>
    </div>

    <div class="text-center q-pa-md">

      <q-btn @click="captureImage" color="grey-10" icon="eva-camera" size="lg" round />

      <div class="row justify-center q-ma-md">
        <q-input v-model="post.caption" class="col col-sm-6" label="Caption" dense />
      </div>

      <div class="row justify-center q-ma-md">
        <q-input v-model="post.location" class="col col-sm-6" label="Location" dense>
          <template v-slot:append>
            <q-btn round dense flat icon="eva-navigation-2-outline" />
          </template>
        </q-input>
      </div>

      <div class="row justify-center q-mt-lg">     
        <q-btn unelevated rounded color="primary" label="Post Image" /> 
      </div>

    </div>   

  </q-page>
</template>

<script>
import { defineComponent } from 'vue'
import { uid } from 'quasar' // Gerador de ID's únicos
require('md-gum-polyfill') // Instalamos a biblioteca Poliyfill (npm install --save md-gum-polyfill)

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
      imageCaptured: false
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
      this.post.photo = canvas.toDataUrl() // Pegando os dados de base64
    }
  },
  mounted () {
    this.initCamera()
  }
})
</script>

<style lang="sass">
.camera-frame
  border: 2px solid $grey-10
  border-radius: 10px
</style>
