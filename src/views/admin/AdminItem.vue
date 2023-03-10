<template>
  <div class="white">
    <NavBar v-bind="menu"></NavBar>
    <div v-if="!this.authorized" class="not_authorized">
      <p>
        You have stumbled into a restricted page. Sorry I can not show it to you
        now.
      </p>
    </div>
    <div v-if="this.authorized">
      <h2>Enter an Item to Celebrate</h2>

      <form @submit.prevent="saveForm">
        <BaseSelect
          label="Celebration Set"
          :options="this.focus_areas"
          v-model="$v.item.celebration_set.$model"
          class="field"
        />
        <BaseInput
          v-model="$v.item.objective.$model"
          label="Objective"
          type="text"
          class="field"
          :class="{ error: $v.item.objective.$error }"
          @blur="$v.item.objective.$touch()"
        />
        <BaseInput
          v-model="$v.item.name.$model"
          label="Item"
          type="text"
          class="field"
          :class="{ error: $v.item.name.$error }"
          @blur="$v.item.name.$touch()"
        />
        <BaseTextarea
          v-model="$v.item.definition.$model"
          label="Standard Definition"
          type="text"
          class="field"
          :class="{ error: $v.item.definition.$error }"
          @blur="$v.item.definition.$touch()"
        />

        <BaseTextarea
          v-model="$v.item.paraphrase.$model"
          label="Paraphrase"
          type="text"
          class="field"
          :class="{ error: $v.item.paraphrase.$error }"
          @blur="$v.item.paraphrase.$touch()"
        />
        <BaseInput
          v-model="$v.item.details.$model"
          label="Any details you want to track?"
          type="text"
          class="field"
        />
        <BaseSelect
          label="Record Number?"
          :options="yes_or_no"
          v-model="$v.item.numbers.$model"
          class="field"
        />
        <div v-if="$v.item.numbers.$model == 'Y'">
          <BaseSelect
            label="Cumulative"
            :options="yes_or_no"
            v-model="$v.item.cumulative.$model"
            class="field"
          />
        </div>
        <div>
          <h3 class="left">Icon:</h3>
          <div v-if="$v.item.image.$model">
            <img
              v-bind:src="
                '/images/icons/' +
                  celebration_set +
                  '/' +
                  $v.item.image.$model.image
              "
              class="icon"
            />
            <br />
          </div>
          <v-select :options="images" label="title" v-model="$v.item.image.$model">
            <template slot="option" slot-scope="option">
              <img :src="'/images/icons/' + celebration_set + '/' + option.image" class="icon" />
              {{ option.title }}
            </template>
          </v-select>
        </div>
      </form>
      <br />
      <br />
      <button class="button green" id="update" @click="saveForm">Update</button>
      <button class="button red" id="delete" @click="deleteForm">Delete</button>
    </div>
  </div>
</template>
<script>
import AuthorService from '@/services/AuthorService.js'
import NavBar from '@/components/NavBar.vue'

import { required } from 'vuelidate/lib/validators'
import { mapState } from 'vuex'
import { authorMixin } from '@/mixins/AuthorMixin.js'
import vSelect from 'vue-select'
// see https://stackoverflow.com/questions/55479380/adding-images-to-vue-select-dropdown
import '@/assets/css/vueSelect.css'
export default {
  computed: mapState(['focus_areas', 'user']),
  props: ['id', 'celebration_set', 'uid', 'tid'],
  components: {
    NavBar,
    'v-select': vSelect
  },
  mixins: [authorMixin],
  data() {
    return {
      images: [],
      directory: null,
      yes_or_no: ['Y', 'N'],
      item: {
        id: null,
        celebration_set: 'personal',
        tid: null,
        uid: null,
        sequence: null,
        page: null,
        objective: null,
        code: null,
        name: null,
        definition: null,
        paraphrase: null,
        details: null,
        numbers: 'Y',
        cumulative: 'Y',
        image: {
          title: 'add',
          image: 'add_48x48.png'
        }
      },
      member: {
        firstname: null,
        lastname: null,
        phone: null,
        scope: null,
        username: null,
        password: null,
        image: 'blank.png'
      }
    }
  },
  validations: {
    item: {
      id: {},
      celebration_set: {},
      tid: {},
      uid: {},
      sequence: {},
      page: {},
      code: {},
      objective: { required },
      name: { required },
      definition: {},
      paraphrase: { required },
      details: {},
      numbers: { required },
      cumulative: { required },
      image: { required }
    }
  },
  methods: {
    async saveForm() {
      if (!this.saved) {
        this.saved = true
        this.disableButton('update')
        this.disableButton('delete')
        var params = {}
        this.item.uid = this.$route.params.uid
        this.item.tid = this.$route.params.tid
        this.item.image = this.item.image.image
        params.item = JSON.stringify(this.item)
        console.log(params)
        var res = await AuthorService.do('updateItem', params)
        //console.log(res)
        this.return()
      }
    },
    async deleteForm() {
      this.disableButton('update')
      this.disableButton('delete')
      var params = {}
      params.uid = this.$route.params.uid
      params.tid = this.$route.params.tid
      params.item = JSON.stringify(this.item)
      console.log(params)
      var res = await AuthorService.do('deleteItem', params)
      console.log(res)
      this.return()
    },

    return() {
      this.$router.push({
        name: 'adminCelebrationSets',
        params: {
          celebration_set: this.$route.params.celebration_set,
        }
      })
    }
  },
  beforeCreate: function() {
    document.body.className = 'admin'
  },
  async created() {
    this.authorized = this.authorize(
      'admin',
      this.$route.params.uid,
      this.$route.params.tid
    )
    if (this.authorized) {
      try {
        this.menu = await this.menuParams('Strategy Item', 'M')
        var params = {}
        params['icons'] = this.$route.params.celebration_set
        params['icon_size'] = '48x48'
        console.log(params)
        var res = await AuthorService.do('getIcons', params)
        console.log(res)
        this.images = res['icons']
        this.directory = res['dir']
        console.log(this.images)
        params['uid'] = this.$route.params.uid
        params['tid'] = this.$route.params.tid
        if (typeof this.$route.params.id != 'undefined') {
          params['id'] = this.$route.params.id
          res = await AuthorService.do('getItem', params)
          if (typeof res != 'undefined') {
            this.item = res
            var im = this.item.image
            this.item.image = {}
            this.item.image.image = im
            this.item.image.title = im.replace('_48x48.png', '')
            console.log(this.item)
          }
        }
      } catch (error) {
        console.log('There was an error in MyItem.vue:', error) // Logs out the error
      }
    }
  }
}
</script>

<style scoped>
img.icon {
  width: 48px;
}
.field {
  padding-top: 20px;
}
</style>
