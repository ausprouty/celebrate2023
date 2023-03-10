<template>
  <div class="white">
    <NavBar v-bind="menu"></NavBar>
    <div v-if="!this.authorized" class="not_authorized">
      <p>
        You have stumbled into a restricted page. Sorry I can not show it to you
        now
      </p>
    </div>
    <div v-if="this.authorized">
      <div v-if="!this.multiple_teams">
        <h1 class="center">{{ this.viewing.team.name }}</h1>
      </div>
      <div v-if="this.multiple_teams">
        <select v-model="current_team">
          <option
            v-for="(team, tid) in viewing.teams"
            v-bind:key="tid"
            :value="team.tid"
          >
            {{ team.name }}
          </option>
        </select>
      </div>
      <div v-if="this.has_members">
        <TeamMemberList
          v-for="teamMember in teamMembers"
          :key="teamMember.uid"
          :teamMember="teamMember"
        />
      </div>
      <div v-if="!this.has_members">
        <h2>There are not any members on this team yet</h2>
      </div>

      <button class="button grey" id="update" @click="newMember">
        Add Members
      </button>
    </div>
  </div>
</template>

<script>
import AuthorService from '@/services/AuthorService.js'
import TeamMemberList from '@/components/TeamMemberList.vue'
import { authorMixin } from '@/mixins/AuthorMixin.js'
import { mapState } from 'vuex'
import NavBar from '@/components/NavBar.vue'

export default {
  props: ['tid', 'uid'],
  computed: mapState(['view', 'user', 'appDir']),
  components: {
    TeamMemberList,
    NavBar
  },
  mixins: [authorMixin],
  data() {
    return {
      authorized: false,
      multiple_teams: false,
      current_team: null,
      has_members: false,
      teamMembers: [
        {
          firstname: null,
          lastname: null,
          uid: null,
          image: 'blank.png'
        }
      ]
    }
  },
  methods: {
    changeTeam(tid) {
      console.log('this is changing the team')
      this.showForm(tid)
    },
    newMember() {
      this.$router.push({
        name: 'teamMemberProfile',
        params: {
          tid: this.$route.params.tid
        }
      })
    },
    async showForm(tid) {
      try {
        this.menu = await this.menuParams('Our Team', 'M')
        var params = this.$route.params
        if (typeof params.uid == 'undefined') {
          params.uid = this.viewing.user.uid
        }
        params.tid = tid
        await this.checkMember(params)
        await this.checkTeam(params)
        await this.checkTeams(params)
        if (this.viewing.teams.length > 1) {
          this.multiple_teams = true
        }
        var route = []
        route['route'] = JSON.stringify(params)
        console.log(route)
        this.teamMembers = await AuthorService.do(
          'getTeamMembersShowingCurrentCelebrations',
          route
        )
        if (this.teamMembers.length > 1) {
          this.has_members = true
        }
        console.log('this.teamMembers')
        console.log(this.teamMembers)
      } catch (error) {
        console.log('There was an error in Team.vue:', error) // Logs out the error
      }
    }
  },
  watch: {
    current_team: function() {
      this.changeTeam(this.current_team)
    }
  },
  beforeCreate: function() {
    document.body.className = 'team'
  },
  async created() {
    this.authorized = this.authorize(
      'team',
      this.user.uid,
      this.$route.params.tid
    )
    if (this.authorized) {
      this.current_team = this.$route.params.tid
      this.showForm(this.current_team)
    }
  }
}
</script>
<style scoped>
select {
  margin-top: 15px;
  margin-bottom: 15px;
}
</style>
