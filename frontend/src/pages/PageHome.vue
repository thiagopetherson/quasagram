<template>
  <q-page class="constrain q-pa-md">

    <div class="row q-col-gutter-lg">
      <div class="col-12 col-sm-8">
        
        <template v-if="posts">
          <q-card v-for="post in posts" :key="post.id" class="card-post q-mb-md" flat bordered>
            <q-item>
              <q-item-section avatar>
                <q-avatar>
                  <img src="https://cdn.quasar.dev/img/boy-avatar.png">
                </q-avatar>
              </q-item-section>         

              <q-item-section>
                <q-item-label class="text-bold">thiago___petherson</q-item-label>
                <q-item-label caption>{{ post.location }}</q-item-label>
              </q-item-section>
            </q-item>

            <q-separator />

              <q-img :src="`http://127.0.0.1:8000/storage/${post.image}`" />
            
            <q-card-section>
              <div>{{ post.caption }}</div>
              <div class="text-caption text-grey">{{ post.created_at }}</div>
            </q-card-section>                        
          </q-card>
        </template>

        <template v-else>
            <h5 class="text-center text-grey">Não temos posts</h5>
        </template>
       
      </div>

      <div class="col-4 large-screen-only">
       <q-item class="fixed">
          <q-item-section avatar>
            <q-avatar size="48px">
              <img src="https://cdn.quasar.dev/img/boy-avatar.png">
            </q-avatar>
          </q-item-section>

          <q-item-section>
            <q-item-label class="text-bold">thiago__petherson</q-item-label>
            <q-item-label caption>
              Thiago Petherson
            </q-item-label>
          </q-item-section>
        </q-item>
      </div>
     
    </div>

  </q-page>
</template>

<script>
import { date } from 'quasar'
import axios from 'axios' // Importando Axios


export default {
  name: 'PageHome',
  data () {
    return {
      posts: []
    }
  },
  methods: {
    getPosts () {
      this.$q.loading.show({ // Chamando o loading
        delay: 400
      })
      // Usamos variáveis de ambientes que guardam nossa url da requisição (configuramos em quasar.conf)
      // ... em build e env
      axios.get(`${ process.env.API }/posts`) // Fazendo a requisição no API
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
  filters: {
    niceDate(value) {
      // Formatar a data
      return date.formatDate(value, 'MMMM D h:mmA')
    }
  },
  created () {
    this.getPosts()
  }
}
</script>

<style lang="sass">
  .card-post
    .q-img
      min-height: 200px
</style>
